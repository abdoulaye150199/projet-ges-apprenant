<?php

namespace App\Controllers;

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../models/model.php';
require_once __DIR__ . '/../services/validator.service.php';
require_once __DIR__ . '/../services/session.service.php';
require_once __DIR__ . '/../enums/profile.enum.php';

// Chargement des messages
$error_messages = require_once __DIR__ . '/../translate/fr/error.fr.php';
$success_messages = require_once __DIR__ . '/../translate/fr/message.fr.php';

use App\Models;
use App\Services;
use App\Enums;

// Affichage de la page de connexion
function login_page() {
    global $session_services;
    
    // L'authentification est déjà vérifiée dans l'index.php
    // Si l'utilisateur arrive ici, c'est qu'il n'est pas connecté
    
    // Rendu de la page de connexion
    render('auth.layout.view.php', 'auth/login.html.php');
}

// Traitement de la connexion
function login_process() {
    global $model, $session_services;
    
    try {
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';

        // Tentative de connexion apprenant
        $result = $model['authenticate_apprenant']($login, $password);
        
        if ($result['success']) {
            // Stocker les données de session
            $session_services['set_session']('user', $result['data']);
            
            // Rediriger vers la page de profil apprenant
            redirect('?page=apprenant-profile');
            return;
        }

        // Si ce n'est pas un apprenant, essayer admin
        $result = $model['authenticate_admin']($login, $password);
        if ($result['success']) {
            $user = $result['data'];
            $session_services['set_session']('user', [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'profile' => $user['profile'],
                'type' => 'admin'
            ]);
            
            redirect('?page=dashboard');
            return;
        }

        // Échec de connexion
        $session_services['set_flash_message']('error', 'Identifiants invalides');
        redirect('?page=login');
        
    } catch (Exception $e) {
        $session_services['set_flash_message']('error', 'Une erreur est survenue');
        redirect('?page=login');
    }
}

// Déconnexion
function logout() {
    // Détruire la session
    session_start();
    session_unset();
    session_destroy();

    // Rediriger vers la page de connexion
    header("Location: ?page=login");
    exit();
}

// Page de mot de passe oublié
function forgot_password_page() {
    global $session_services;
    
    $session_services['start_session']();
    
    // Si l'utilisateur est déjà connecté, redirection vers le tableau de bord
    if ($session_services['is_logged_in']()) {
        redirect('?page=dashboard');
    }
    
    render('auth.layout.view.php', 'auth/forgot_password.html.php');
}

// Traitement de la demande de réinitialisation de mot de passe
function forgot_password_process() {
    global $model, $validator_services, $session_services, $error_messages;
    
    $session_services['start_session']();
    
    // Si l'utilisateur est déjà connecté, redirection vers le tableau de bord
    if ($session_services['is_logged_in']()) {
        redirect('?page=dashboard');
    }
    
    $email = $_POST['email'] ?? '';
    
    // Validation de l'email
    $errors = [];
    
    if ($validator_services['is_empty']($email)) {
        $errors['email'] = $error_messages['form']['required'];
    } elseif (!$validator_services['is_email']($email)) {
        $errors['email'] = $error_messages['form']['email'];
    }
    
    // S'il y a des erreurs, affichage du formulaire avec les erreurs
    if (!empty($errors)) {
        render('auth.layout.view.php', 'auth/forgot_password.html.php', [
            'errors' => $errors,
            'email' => $email
        ]);
        return;
    }
    
    // Recherche de l'utilisateur par email
    $data = $model['read_data']();
    
    // Vérifier si des utilisateurs existent et trouver celui avec l'email correspondant
    $user_found = false;
    $user_index = -1;
    
    if (isset($data['users']) && is_array($data['users'])) {
        $user_indices = array_keys(array_filter($data['users'], function($user) use ($email) {
            return isset($user['email']) && $user['email'] === $email;
        }));
        
        if (!empty($user_indices)) {
            $user_found = true;
            $user_index = reset($user_indices);
        }
    }
    
    if ($user_found) {
        // Générer un token de réinitialisation (simple pour ce projet éducatif)
        $token = md5($email . time());
        
        // Enregistrer le token dans les données utilisateur
        $data['users'][$user_index]['reset_token'] = $token;
        $data['users'][$user_index]['reset_expire'] = time() + 3600; // valide 1 heure
        
        if ($model['write_data']($data)) {
            // Dans un environnement réel, on enverrait un email
            // Pour ce projet, on redirige directement vers la page de réinitialisation
            $session_services['set_flash_message']('info', 'Vous pouvez maintenant réinitialiser votre mot de passe');
            redirect('?page=reset-password&token=' . $token);
            return;
        }
    }
    
    // Même message que l'utilisateur existe ou non (sécurité)
    $session_services['set_flash_message']('info', 'Si cette adresse email est associée à un compte, vous recevrez un lien de réinitialisation.');
    redirect('?page=login');
}

// Page de réinitialisation de mot de passe
function reset_password_page() {
    global $model, $session_services;
    
    $session_services['start_session']();
    
    // Si l'utilisateur est déjà connecté, redirection vers le tableau de bord
    if ($session_services['is_logged_in']()) {
        redirect('?page=dashboard');
    }
    
    $token = $_GET['token'] ?? '';
    
    if (empty($token)) {
        $session_services['set_flash_message']('danger', 'Lien de réinitialisation invalide');
        redirect('?page=login');
        return;
    }
    
    // Vérification de la validité du token
    $data = $model['read_data']();
    
    $user_found = false;
    $user_indices = array_keys(array_filter($data['users'], function($user) use ($token) {
        return isset($user['reset_token']) && 
               $user['reset_token'] === $token && 
               isset($user['reset_expire']) && 
               $user['reset_expire'] > time();
    }));
    
    if (empty($user_indices)) {
        $session_services['set_flash_message']('danger', 'Lien de réinitialisation invalide ou expiré');
        redirect('?page=login');
        return;
    }
    
    render('auth.layout.view.php', 'auth/reset_password.html.php', [
        'token' => $token
    ]);
}

// Traitement de la réinitialisation de mot de passe
function reset_password_process() {
    global $model, $validator_services, $session_services, $error_messages, $success_messages;
    
    $session_services['start_session']();
    
    // Si l'utilisateur est déjà connecté, redirection vers le tableau de bord
    if ($session_services['is_logged_in']()) {
        redirect('?page=dashboard');
    }
    
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation des données
    $errors = [];
    
    if ($validator_services['is_empty']($new_password)) {
        $errors['new_password'] = $error_messages['form']['required'];
    } elseif (!$validator_services['min_length']($new_password, 6)) {
        $errors['new_password'] = sprintf($error_messages['form']['min_length'], 6);
    }
    
    if ($validator_services['is_empty']($confirm_password)) {
        $errors['confirm_password'] = $error_messages['form']['required'];
    } elseif ($new_password !== $confirm_password) {
        $errors['confirm_password'] = 'Les mots de passe ne correspondent pas';
    }
    
    // S'il y a des erreurs, affichage du formulaire avec les erreurs
    if (!empty($errors)) {
        render('auth.layout.view.php', 'auth/reset_password.html.php', [
            'errors' => $errors,
            'token' => $token
        ]);
        return;
    }
    
    // Vérification de la validité du token et mise à jour du mot de passe
    $data = $model['read_data']();
    
    $user_indices = array_keys(array_filter($data['users'], function($user) use ($token) {
        return isset($user['reset_token']) && 
               $user['reset_token'] === $token && 
               isset($user['reset_expire']) && 
               $user['reset_expire'] > time();
    }));
    
    if (empty($user_indices)) {
        $session_services['set_flash_message']('danger', 'Lien de réinitialisation invalide ou expiré');
        redirect('?page=login');
        return;
    }
    
    $user_index = reset($user_indices);
    
    // Mise à jour du mot de passe (sans cryptage pour simplifier ce projet éducatif)
    $data['users'][$user_index]['password'] = $new_password;
    
    // Suppression du token de réinitialisation
    unset($data['users'][$user_index]['reset_token']);
    unset($data['users'][$user_index]['reset_expire']);
    
    if ($model['write_data']($data)) {
        $session_services['set_flash_message']('success', 'Votre mot de passe a été modifié avec succès');
        redirect('?page=login');
    } else {
        $session_services['set_flash_message']('danger', 'Erreur lors de la mise à jour du mot de passe');
        render('auth.layout.view.php', 'auth/reset_password.html.php', [
            'token' => $token
        ]);
    }
}

// Page de changement de mot de passe
function change_password_page() {
    $user = check_auth();
    
    render('auth.layout.view.php', 'auth/change_password.html.php', [
        'user' => $user
    ]);
}

// Traitement du changement de mot de passe
function change_password_process() {
    global $model, $validator_services, $session_services, $error_messages, $success_messages;
    
    $user = check_auth();
    
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation des données
    $errors = [];
    
    if ($validator_services['is_empty']($current_password)) {
        $errors['current_password'] = $error_messages['form']['required'];
    }
    
    if ($validator_services['is_empty']($new_password)) {
        $errors['new_password'] = $error_messages['form']['required'];
    } elseif (!$validator_services['min_length']($new_password, 6)) {
        $errors['new_password'] = sprintf($error_messages['form']['min_length'], 6);
    }
    
    if ($validator_services['is_empty']($confirm_password)) {
        $errors['confirm_password'] = $error_messages['form']['required'];
    } elseif ($new_password !== $confirm_password) {
        $errors['confirm_password'] = 'Les mots de passe ne correspondent pas';
    }
    
    // Vérification du mot de passe actuel
    if (empty($errors['current_password']) && $current_password !== $user['password']) {
        $errors['current_password'] = 'Mot de passe actuel incorrect';
    }
    
    // S'il y a des erreurs, affichage du formulaire avec les erreurs
    if (!empty($errors)) {
        render('auth.layout.view.php', 'auth/change_password.html.php', [
            'user' => $user,
            'errors' => $errors
        ]);
        return;
    }
    
    // Changement du mot de passe
    $data = $model['read_data']();
    
    $user_indices = array_keys(array_filter($data['users'], function($u) use ($user) {
        return $u['id'] === $user['id'];
    }));
    
    if (empty($user_indices)) {
        $session_services['set_flash_message']('danger', 'Erreur lors du changement de mot de passe');
        render('auth.layout.view.php', 'auth/change_password.html.php', [
            'user' => $user
        ]);
        return;
    }
    
    $user_index = reset($user_indices);
    
    // Mise à jour du mot de passe (sans cryptage pour ce projet éducatif)
    $data['users'][$user_index]['password'] = $new_password;
    
    if ($model['write_data']($data)) {
        // Mise à jour de l'utilisateur en session
        $user['password'] = $new_password;
        $session_services['set_session']('user', $user);
        
        $session_services['set_flash_message']('success', 'Votre mot de passe a été modifié avec succès');
        redirect('?page=dashboard');
    } else {
        $session_services['set_flash_message']('danger', 'Erreur lors du changement de mot de passe');
        render('auth.layout.view.php', 'auth/change_password.html.php', [
            'user' => $user
        ]);
    }
}

// Page d'erreur 403 (Accès interdit)
function forbidden() {
    render('auth.layout.view.php', 'error/403.view.php');
}

// Page d'erreur 404 (Page non trouvée)
function not_found() {
    render('auth.layout.view.php', 'error/404.view.php');
}