<?php

namespace App\Middleware;

require_once __DIR__ . '/../services/session.service.php';

function check_apprenant_auth() {
    global $session_services;
    
    $session_services['start_session']();
    
    if (!$session_services['is_logged_in']()) {
        redirect('?page=login');
        return false;
    }
    
    $user = $session_services['get_current_user']();
    if ($user['type'] !== 'apprenant') {
        redirect('?page=forbidden');
        return false;
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