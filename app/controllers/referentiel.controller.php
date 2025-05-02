<?php

namespace App\Controllers;

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../models/model.php';
require_once __DIR__ . '/../services/validator.service.php';
require_once __DIR__ . '/../services/session.service.php';
require_once __DIR__ . '/../translate/fr/error.fr.php';
require_once __DIR__ . '/../translate/fr/message.fr.php';
require_once __DIR__ . '/../enums/profile.enum.php';

use App\Models;
use App\Services;
use App\Translate\fr;
use App\Enums;
use Exception; // Ajoutez cette ligne

// Affichage de la liste des référentiels de la promotion en cours
function list_referentiels() {
    global $model, $session_services;
    
    try {
        // Vérifier l'authentification
        $user = $session_services['get_current_user']();
        if (!$user || !in_array($user['profile'], ['Admin', 'Attache'])) {
            redirect('?page=forbidden');
            return;
        }
        
        // Récupérer la promotion courante
        $current_promotion = $model['get_current_promotion']();
        
        // Si aucune promotion n'est active
        if (!$current_promotion) {
            $session_services['set_flash_message']('info', 'Aucune promotion active');
            redirect('?page=promotions');
            return;
        }
        
        // Récupération des référentiels de la promotion courante
        $referentiels = $model['get_referentiels_by_promotion']($current_promotion['id']);
        
        // Filtrage des référentiels selon le critère de recherche
        $search = $_GET['search'] ?? '';
        if (!empty($search)) {
            $referentiels = array_filter($referentiels, function ($referentiel) use ($search) {
                return stripos($referentiel['name'], $search) !== false || 
                       stripos($referentiel['description'], $search) !== false;
            });
        }
        
        // Affichage de la vue
        render('admin.layout.view.php', 'referentiel/ref-list.view.php', [
            'user' => $user,
            'current_promotion' => $current_promotion,
            'referentiels' => $referentiels,  
            'search' => $search
        ]);
        
    } catch (Exception $e) {
        $session_services['set_flash_message']('danger', 'Une erreur est survenue');
        redirect('?page=dashboard');
    }
}

// Affichage de la liste de tous les référentiels
function list_all_referentiels() {
    global $model;
    
    // Vérification si l'utilisateur est connecté
    check_auth();
    
    // Récupérer les paramètres
    $search = $_GET['search'] ?? '';
    $current_page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
    $items_per_page = 6;
    
    // Récupérer tous les référentiels
    $referentiels = $model['get_all_referentiels']();
    
    // Filtrer par recherche si nécessaire
    if (!empty($search)) {
        $referentiels = array_filter($referentiels, function($ref) use ($search) {
            return stripos($ref['name'], $search) !== false || 
                   stripos($ref['description'], $search) !== false;
        });
    }
    
    // Calculer la pagination
    $total_items = count($referentiels);
    $total_pages = max(1, ceil($total_items / $items_per_page));
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $items_per_page;
    
    // Extraire les référentiels pour la page courante
    $paginated_referentiels = array_slice(array_values($referentiels), $offset, $items_per_page);
    
    // Debug temporaire
    error_log("Debug - Total items: $total_items, Total pages: $total_pages, Current page: $current_page");
    
    // Rendre la vue avec toutes les variables nécessaires
    render('admin.layout.view.php', 'referentiel/ref-list-all.view.php', [
        'referentiels' => $paginated_referentiels,
        'search' => $search,
        'page' => $current_page,
        'pages' => $total_pages,
        'active_menu' => 'referentiels'
    ]);
}

// Affichage du formulaire d'ajout d'un référentiel
function add_referentiel_form() {
    render('admin.layout.view.php', 'referentiel/ref-add.view.php', [
        'active_menu' => 'referentiels'
    ]);
}

// Traitement de l'ajout d'un référentiel
function add_referentiel_process() {
    global $model, $session_services;
    
    try {
        // Vérification des droits d'accès
        check_profile(Enums\ADMIN);
        
        // Récupération des données du formulaire
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);
        $sessions = (int)($_POST['sessions'] ?? 0);
        
        // Validation des données
        $errors = [];
        
        // Validation du nom (requis et unique)
        if (empty($name)) {
            $errors['name'] = 'Le nom est requis';
        } else {
            // Vérifier si le nom est unique
            $existing = $model['get_referentiel_by_name']($name);
            if ($existing) {
                $errors['name'] = 'Ce nom de référentiel existe déjà';
            }
        }
        
        // Validation de la description
        if (empty($description)) {
            $errors['description'] = 'La description est requise';
        }
        
        // Validation de la capacité
        if ($capacity <= 0) {
            $errors['capacity'] = 'La capacité doit être supérieure à 0';
        }
        
        // Validation du nombre de sessions
        if ($sessions <= 0) {
            $errors['sessions'] = 'Le nombre de sessions doit être supérieur à 0';
        }
        
        // Validation de l'image
        $image_path = 'assets/images/referentiels/default.jpg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $max_size = 2 * 1024 * 1024; // 2MB en bytes
            
            $filename = $_FILES['image']['name'];
            $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $filesize = $_FILES['image']['size'];
            
            // Vérification du format
            if (!in_array($filetype, $allowed)) {
                $errors['image'] = 'Format invalide. Formats acceptés : JPG, PNG';
            }
            
            // Vérification de la taille
            if ($filesize > $max_size) {
                $errors['image'] = 'L\'image ne doit pas dépasser 2MB';
            }
            
            // Upload si pas d'erreur
            if (!isset($errors['image'])) {
                $new_filename = uniqid() . '.' . $filetype;
                $upload_path = 'assets/images/referentiels/' . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_path = $upload_path;
                } else {
                    $errors['image'] = 'Erreur lors de l\'upload de l\'image';
                }
            }
        }
        
        // Si pas d'erreurs, création du référentiel
        if (empty($errors)) {
            $referentiel_data = [
                'id' => uniqid(),
                'name' => $name,
                'description' => $description,
                'capacite' => $capacity,
                'sessions' => $sessions,
                'image' => $image_path,
                'modules' => []
            ];
            
            $result = $model['create_referentiel']($referentiel_data);
            
            if ($result) {
                // Définir le message de succès et rediriger
                $session_services['set_flash_message']('success', 'Le référentiel a été créé avec succès');
                redirect('?page=all-referentiels');
                return;
            }
        }
        
        // En cas d'erreurs, retourner au formulaire avec les erreurs
        render('admin.layout.view.php', 'referentiel/ref-add.view.php', [
            'active_menu' => 'referentiels',
            'errors' => $errors,
            'name' => $name,
            'description' => $description,
            'capacity' => $capacity,
            'sessions' => $sessions
        ]);
        
    } catch (Exception $e) {
        $session_services['set_flash_message']('danger', 'Une erreur est survenue lors de la création du référentiel');
        redirect('?page=all-referentiels');
    }
}

// Affichage du formulaire d'affectation de référentiels à une promotion
function assign_referentiels_form() {
    global $model, $session_services;
    
    // Vérification des droits d'accès (Admin uniquement)
    $user = check_profile(Enums\ADMIN);
    
    // Récupération de la promotion courante
    $current_promotion = $model['get_current_promotion']();
    
    if (!$current_promotion) {
        $session_services['set_flash_message']('info', 'Aucune promotion active. Veuillez d\'abord activer une promotion.');
        redirect('?page=promotions');
        return;
    }
    
    // Vérifier si la promotion est en cours ou terminée
    if ($current_promotion['etat'] === 'terminee') {
        $session_services['set_flash_message']('warning', 'L\'assignation n\'est possible qu\'avec la promotion en cours.');
        redirect('?page=referentiels');
        return;
    }
    
    // Le reste du code reste inchangé
    // Récupération de tous les référentiels
    $all_referentiels = $model['get_all_referentiels']();
    
    // Récupération des référentiels déjà affectés à la promotion
    $assigned_referentiels = $model['get_referentiels_by_promotion']($current_promotion['id']);
    $assigned_ids = array_map(function($ref) {
        return $ref['id'];
    }, $assigned_referentiels);
    
    // Filtrer les référentiels non affectés
    $unassigned_referentiels = array_filter($all_referentiels, function($ref) use ($assigned_ids) {
        return !in_array($ref['id'], $assigned_ids);
    });
    
    // Affichage de la vue
    render('admin.layout.view.php', 'referentiel/ref-assign.view.php', [
        'user' => $user,
        'current_promotion' => $current_promotion,
        'unassigned_referentiels' => array_values($unassigned_referentiels),
        'assigned_referentiels' => $assigned_referentiels
    ]);
}

function unassign_referentiel_process() {
    global $model, $session_services;
    
    // Vérification des droits d'accès (Admin uniquement)
    check_profile(Enums\ADMIN);
    
    // Récupération de la promotion courante
    $current_promotion = $model['get_current_promotion']();
    
    if (!$current_promotion) {
        $session_services['set_flash_message']('info', 'Aucune promotion active.');
        redirect('?page=referentiels');
        return;
    }
    
    // Vérifier si la promotion est en cours ou terminée
    if ($current_promotion['etat'] === 'terminee') {
        $session_services['set_flash_message']('warning', 'La désaffectation n\'est possible qu\'avec la promotion en cours.');
        redirect('?page=referentiels');
        return;
    }
    
    // Le reste du code reste inchangé
    // Récupération de l'ID du référentiel à désaffecter
    $referentiel_id = $_POST['referentiel_id'] ?? '';
    
    if (empty($referentiel_id)) {
        $session_services['set_flash_message']('danger', 'Référentiel non spécifié.');
        redirect('?page=assign-referentiels');
        return;
    }
    
    // Vérifier si le référentiel a des apprenants
    $has_apprenants = $model['referentiel_has_apprenants']($current_promotion['id'], $referentiel_id);
    
    if ($has_apprenants) {
        $session_services['set_flash_message']('danger', 'Impossible de désaffecter un référentiel qui contient des apprenants.');
        redirect('?page=assign-referentiels');
        return;
    }
    
    // Désaffectation du référentiel
    $result = $model['unassign_referentiel_from_promotion']($current_promotion['id'], $referentiel_id);
    
    if ($result) {
        $session_services['set_flash_message']('success', 'Référentiel désaffecté avec succès.');
    } else {
        $session_services['set_flash_message']('danger', 'Erreur lors de la désaffectation du référentiel.');
    }
    
    redirect('?page=assign-referentiels');
}

// Traitement de l'affectation de référentiels à une promotion
function assign_referentiels_process() {
    global $model, $session_services;
    
    try {
        check_profile(Enums\ADMIN);
        
        $current_promotion = $model['get_current_promotion']();
        if (!$current_promotion) {
            $session_services['set_flash_message']('info', 'Aucune promotion active');
            redirect('?page=promotions');
            return;
        }
        
        // Récupérer les référentiels existants
        $existing_referentiels = $current_promotion['referentiels'] ?? [];
        
        // Récupérer les nouveaux référentiels sélectionnés
        $selected_referentiels = $_POST['referentiels'] ?? [];
        if (empty($selected_referentiels)) {
            $session_services['set_flash_message']('info', 'Aucun référentiel sélectionné');
            redirect('?page=assign-referentiels');
            return;
        }
        
        // S'assurer que selected_referentiels est un tableau
        if (!is_array($selected_referentiels)) {
            $selected_referentiels = [$selected_referentiels];
        }
        
        // Fusionner les référentiels existants avec les nouveaux
        $all_referentiels = array_merge($existing_referentiels, $selected_referentiels);
        
        // Assigner tous les référentiels
        $result = $model['assign_referentiels_to_promotion']($current_promotion['id'], $all_referentiels);
        
        if ($result) {
            $session_services['set_flash_message']('success', 'Référentiels assignés avec succès');
        } else {
            $session_services['set_flash_message']('error', 'Erreur lors de l\'assignation des référentiels');
        }
        
        redirect('?page=assign-referentiels');
        
    } catch (Exception $e) {
        $session_services['set_flash_message']('error', 'Une erreur est survenue');
        redirect('?page=referentiels');
    }
}

function assign_referentiels_to_promotion() {
    global $model, $session_services;
    
    $promotion_id = $_POST['promotion_id'] ?? null;
    $referentiel_ids = $_POST['referentiel_ids'] ?? [];
    
    if (!$promotion_id || !is_array($referentiel_ids)) {
        $session_services['set_flash_message']('error', 'Données invalides');
        redirect('?page=referentiels');
        return;
    }
    
    $result = $model['assign_referentiels_to_promotion']($promotion_id, $referentiel_ids);
    
    if ($result) {
        $session_services['set_flash_message']('success', 'Référentiels assignés avec succès');
    } else {
        $session_services['set_flash_message']('error', 'Erreur lors de l\'assignation');
    }
    
    redirect('?page=referentiels');
}