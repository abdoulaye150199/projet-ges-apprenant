<?php

namespace App\Middleware;

require_once __DIR__ . '/../services/session.service.php';

function check_apprenant_auth() {
    global $session_services;
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Récupérer l'utilisateur en session
    $user = $session_services['get_session']('user');
    
    // Vérifier si l'utilisateur est un apprenant
    if (!$user || $user['type'] !== 'apprenant') {
        // Stocker un message d'erreur
        $session_services['set_flash_message']('error', 'Accès non autorisé');
        
        // Rediriger vers la page de connexion
        header('Location: ?page=login&type=apprenant');
        exit();
    }
    
    return $user;
}

/**
 * Vérifie si l'utilisateur est authentifié
 * 
 * @return boolean
 */
function is_authenticated() {
    global $session_services;
    
    if (!$session_services['is_session_started']()) {
        $session_services['start_session']();
    }
    
    return $session_services['is_logged_in']();
}

/**
 * Vérifie si l'utilisateur est un apprenant
 * 
 * @return boolean
 */
function is_apprenant() {
    global $session_services;
    
    $user = $session_services['get_session']('user');
    return $user && $user['type'] === 'apprenant';
}