<?php

namespace App\Route;

require_once __DIR__ . '/../controllers/auth.controller.php';
require_once __DIR__ . '/../controllers/promotion.controller.php';
require_once __DIR__ . '/../controllers/referentiel.controller.php';
require_once __DIR__ . '/../controllers/dashboard.controller.php';
require_once __DIR__ . '/../controllers/apprenant.controller.php';

use App\Controllers;

// Définition de toutes les routes disponibles avec leur fonction controller associée
$routes = [
    // Routes pour l'authentification
    'login' => 'App\Controllers\login_page',
    'login-process' => 'App\Controllers\login_process',
    'logout' => 'App\Controllers\logout',
    'change-password' => 'App\Controllers\change_password_page',
    'change-password-process' => 'App\Controllers\change_password_process',
    'forgot-password' => 'App\Controllers\forgot_password_page',
    'forgot-password-process' => 'App\Controllers\forgot_password_process',
    'reset-password' => 'App\Controllers\reset_password_page',
    'reset-password-process' => 'App\Controllers\reset_password_process',
    
    // Routes pour les promotions
    'promotions' => 'App\Controllers\list_promotions',
    'add-promotion' => 'App\Controllers\add_promotion_form',
    'add-promotion-process' => 'App\Controllers\add_promotion_process',
    'toggle-promotion-status' => 'App\Controllers\toggle_promotion_status',
    'promotion' => 'App\Controllers\promotion_page',
    'add_promotion_form' => 'App\Controllers\add_promotion_form',
    'add_promotion' => 'App\Controllers\add_promotion',
    'toggle_promotion' => 'App\Controllers\toggle_promotion_status',
    'search_referentiels' => 'App\Controllers\search_referentiels',
    
    // Routes pour les référentiels
    'referentiels' => 'App\Controllers\list_referentiels',
    'all-referentiels' => 'App\Controllers\list_all_referentiels',
    'add-referentiel' => 'App\Controllers\add_referentiel_form',
    'add-referentiel-process' => 'App\Controllers\add_referentiel_process',
    'assign-referentiels' => 'App\Controllers\assign_referentiels_form',
    'assign-referentiels-process' => 'App\Controllers\assign_referentiels_process',
    'unassign-referentiel' => 'App\Controllers\unassign_referentiel_process',
    
    // Route par défaut pour le tableau de bord
    'dashboard' => 'App\Controllers\dashboard',
    
    // Route pour les erreurs
    'forbidden' => 'App\Controllers\forbidden',
    
    // Route par défaut (page non trouvée)
    '404' => 'App\Controllers\not_found',

    // Routes pour les apprenants
    'apprenants' => 'App\Controllers\list_apprenants',
    'add-apprenant' => 'App\Controllers\add_apprenant_form',
    'add-apprenant-process' => 'App\Controllers\add_apprenant_process',
    'edit-apprenant' => 'App\Controllers\edit_apprenant_form',
    'edit-apprenant-process' => 'App\Controllers\edit_apprenant_process',
    'delete-apprenant' => 'App\Controllers\delete_apprenant_process',
    'download-list' => 'App\Controllers\download_list',
    'apprenant-details' => 'App\Controllers\show_apprenant_details',
    'exclude-apprenant' => 'App\Controllers\exclude_apprenant',
    'import-apprenants' => 'App\Controllers\import_apprenants_form',
    'import-apprenants-process' => 'App\Controllers\import_apprenants_process',
    'apprenant-profile' => 'App\Controllers\show_apprenant_profile',

    // Routes pour les templates
    'fill-template' => 'App\Controllers\fill_template_form',
    'fill-template-process' => 'App\Controllers\fill_template_process',
];

// Liste des pages qui ne nécessitent pas d'authentification
$public_pages = ['login', 'login-process', 'forgot-password', 'forgot-password-process', 'reset-password', 'reset-password-process'];

/**
 * Fonction principale de gestion des requêtes
 * Cette fonction démarre la session et route la requête vers le contrôleur approprié
 */
function handle_request() {
    global $routes, $public_pages;
    
    // Démarrer la session
    global $session_services;
    $session_services['start_session']();
    
    // Récupération de la page demandée
    $page = isset($_GET['page']) ? $_GET['page'] : 'login';
    
    error_log("Page demandée : $page, Utilisateur connecté : " . ($session_services['is_logged_in']() ? 'Oui' : 'Non'));
    
    // Si l'utilisateur est connecté et qu'il essaie d'accéder à la page de connexion, rediriger vers le dashboard
    if ($session_services['is_logged_in']() && in_array($page, $public_pages)) {
        header('Location: ?page=dashboard');
        exit;
    }
    
    // Vérifier si la page nécessite une authentification
    if (!$session_services['is_logged_in']() && !in_array($page, $public_pages)) {
        header('Location: ?page=login');
        exit;
    }
    
    // Routage vers le contrôleur approprié
    route($page);
}

/**
 * Fonction de routage qui exécute le contrôleur correspondant à la page demandée
 *
 * @param string $page La page demandée
 * @return mixed Le résultat de la fonction contrôleur
 */
function route($page) {
    global $routes;
    
    // Vérifie si la route demandée existe
    $route_exists = array_key_exists($page, $routes);
    
    // Obtient la fonction à exécuter
    $controller_function = $route_exists ? $routes[$page] : $routes['404'];
    
    // Exécute le contrôleur
    if (is_callable($controller_function)) {
        return call_user_func($controller_function);
    }
    
    // Si la route n'existe pas, redirige vers la page 404
    return call_user_func($routes['404']);
}