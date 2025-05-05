<?php

namespace App\Route;

require_once __DIR__ . '/../controllers/auth.controller.php';
require_once __DIR__ . '/../controllers/promotion.controller.php';
require_once __DIR__ . '/../controllers/referentiel.controller.php';
require_once __DIR__ . '/../controllers/dashboard.controller.php';
require_once __DIR__ . '/../controllers/apprenant.controller.php';

use App\Controllers;

// Définition de l'enum pour les routes
enum RouteEnum: string {
    // Auth routes
    case LOGIN = 'login';
    case LOGIN_PROCESS = 'login-process';
    case LOGOUT = 'logout';
    case CHANGE_PASSWORD = 'change-password';
    case CHANGE_PASSWORD_PROCESS = 'change-password-process';
    case FORGOT_PASSWORD = 'forgot-password';
    case FORGOT_PASSWORD_PROCESS = 'forgot-password-process';
    case RESET_PASSWORD = 'reset-password';
    case RESET_PASSWORD_PROCESS = 'reset-password-process';

    // Promotion routes
    case PROMOTIONS = 'promotions';
    case ADD_PROMOTION = 'add-promotion';
    case ADD_PROMOTION_PROCESS = 'add-promotion-process';
    case TOGGLE_PROMOTION_STATUS = 'toggle-promotion-status';
    case PROMOTION = 'promotion';
    case SEARCH_REFERENTIELS = 'search_referentiels';

    // Referentiel routes
    case REFERENTIELS = 'referentiels';
    case ALL_REFERENTIELS = 'all-referentiels';
    case ADD_REFERENTIEL = 'add-referentiel';
    case ADD_REFERENTIEL_PROCESS = 'add-referentiel-process';
    case ASSIGN_REFERENTIELS = 'assign-referentiels';
    case ASSIGN_REFERENTIELS_PROCESS = 'assign-referentiels-process';
    case UNASSIGN_REFERENTIEL = 'unassign-referentiel';

    // Dashboard route
    case DASHBOARD = 'dashboard';

    // Error routes
    case FORBIDDEN = 'forbidden';
    case NOT_FOUND = '404';

    // Apprenant routes
    case APPRENANTS = 'apprenants';
    case ADD_APPRENANT = 'add-apprenant';
    case ADD_APPRENANT_PROCESS = 'add-apprenant-process';
    case EDIT_APPRENANT = 'edit-apprenant';
    case EDIT_APPRENANT_PROCESS = 'edit-apprenant-process';
    case DELETE_APPRENANT = 'delete-apprenant';
    case DOWNLOAD_LIST = 'download-list';
    case APPRENANT_DETAILS = 'apprenant-details';
    case EXCLUDE_APPRENANT = 'exclude-apprenant';
    case IMPORT_APPRENANTS = 'import-apprenants';
    case IMPORT_APPRENANTS_PROCESS = 'import-apprenants-process';
    case APPRENANT_PROFILE = 'apprenant-profile';

    // Template routes
    case FILL_TEMPLATE = 'fill-template';
    case FILL_TEMPLATE_PROCESS = 'fill-template-process';
}

// Mapping des routes vers les contrôleurs
$routes = [
    RouteEnum::LOGIN->value => 'App\Controllers\login_page',
    RouteEnum::LOGIN_PROCESS->value => 'App\Controllers\login_process',
    RouteEnum::LOGOUT->value => 'App\Controllers\logout',
    RouteEnum::CHANGE_PASSWORD->value => 'App\Controllers\change_password_page',
    RouteEnum::CHANGE_PASSWORD_PROCESS->value => 'App\Controllers\change_password_process',
    RouteEnum::FORGOT_PASSWORD->value => 'App\Controllers\forgot_password_page',
    RouteEnum::FORGOT_PASSWORD_PROCESS->value => 'App\Controllers\forgot_password_process',
    RouteEnum::RESET_PASSWORD->value => 'App\Controllers\reset_password_page',
    RouteEnum::RESET_PASSWORD_PROCESS->value => 'App\Controllers\reset_password_process',
    RouteEnum::PROMOTIONS->value => 'App\Controllers\list_promotions',
    RouteEnum::ADD_PROMOTION->value => 'App\Controllers\add_promotion_form',
    RouteEnum::ADD_PROMOTION_PROCESS->value => 'App\Controllers\add_promotion_process',
    RouteEnum::TOGGLE_PROMOTION_STATUS->value => 'App\Controllers\toggle_promotion_status',
    RouteEnum::PROMOTION->value => 'App\Controllers\promotion_page',
    RouteEnum::SEARCH_REFERENTIELS->value => 'App\Controllers\search_referentiels',
    RouteEnum::REFERENTIELS->value => 'App\Controllers\list_referentiels',
    RouteEnum::ALL_REFERENTIELS->value => 'App\Controllers\list_all_referentiels',
    RouteEnum::ADD_REFERENTIEL->value => 'App\Controllers\add_referentiel_form',
    RouteEnum::ADD_REFERENTIEL_PROCESS->value => 'App\Controllers\add_referentiel_process',
    RouteEnum::ASSIGN_REFERENTIELS->value => 'App\Controllers\assign_referentiels_form',
    RouteEnum::ASSIGN_REFERENTIELS_PROCESS->value => 'App\Controllers\assign_referentiels_process',
    RouteEnum::UNASSIGN_REFERENTIEL->value => 'App\Controllers\unassign_referentiel_process',
    RouteEnum::DASHBOARD->value => 'App\Controllers\dashboard',
    RouteEnum::FORBIDDEN->value => 'App\Controllers\forbidden',
    RouteEnum::NOT_FOUND->value => 'App\Controllers\not_found',
    RouteEnum::APPRENANTS->value => 'App\Controllers\list_apprenants',
    RouteEnum::ADD_APPRENANT->value => 'App\Controllers\add_apprenant_form',
    RouteEnum::ADD_APPRENANT_PROCESS->value => 'App\Controllers\add_apprenant_process',
    RouteEnum::EDIT_APPRENANT->value => 'App\Controllers\edit_apprenant_form',
    RouteEnum::EDIT_APPRENANT_PROCESS->value => 'App\Controllers\edit_apprenant_process',
    RouteEnum::DELETE_APPRENANT->value => 'App\Controllers\delete_apprenant_process',
    RouteEnum::DOWNLOAD_LIST->value => 'App\Controllers\download_list',
    RouteEnum::APPRENANT_DETAILS->value => 'App\Controllers\show_apprenant_details',
    RouteEnum::EXCLUDE_APPRENANT->value => 'App\Controllers\exclude_apprenant',
    RouteEnum::IMPORT_APPRENANTS->value => 'App\Controllers\import_apprenants_form',
    RouteEnum::IMPORT_APPRENANTS_PROCESS->value => 'App\Controllers\import_apprenants_process',
    RouteEnum::APPRENANT_PROFILE->value => 'App\Controllers\show_apprenant_profile',
    RouteEnum::FILL_TEMPLATE->value => 'App\Controllers\fill_template_form',
    RouteEnum::FILL_TEMPLATE_PROCESS->value => 'App\Controllers\fill_template_process',
];

// Pages publiques
$public_pages = [
    RouteEnum::LOGIN->value,
    RouteEnum::LOGIN_PROCESS->value,
    RouteEnum::FORGOT_PASSWORD->value,
    RouteEnum::FORGOT_PASSWORD_PROCESS->value,
    RouteEnum::RESET_PASSWORD->value,
    RouteEnum::RESET_PASSWORD_PROCESS->value
];

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
    $page = $_GET['page'] ?? RouteEnum::LOGIN->value;
    
    error_log("Page demandée : $page, Utilisateur connecté : " . ($session_services['is_logged_in']() ? 'Oui' : 'Non'));
    
    // Si l'utilisateur est connecté et qu'il essaie d'accéder à la page de connexion, rediriger vers le dashboard
    if ($session_services['is_logged_in']() && in_array($page, $public_pages)) {
        header('Location: ?page=' . RouteEnum::DASHBOARD->value);
        exit;
    }
    
    // Vérifier si la page nécessite une authentification
    if (!$session_services['is_logged_in']() && !in_array($page, $public_pages)) {
        header('Location: ?page=' . RouteEnum::LOGIN->value);
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
    $controller_function = $route_exists ? $routes[$page] : $routes[RouteEnum::NOT_FOUND->value];
    
    // Exécute le contrôleur
    if (is_callable($controller_function)) {
        return call_user_func($controller_function);
    }
    
    // Si la route n'existe pas, redirige vers la page 404
    return call_user_func($routes[RouteEnum::NOT_FOUND->value]);
}