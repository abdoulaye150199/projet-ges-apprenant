<?php

namespace App\Controllers;

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../models/model.php';
require_once __DIR__ . '/../services/session.service.php';
require_once __DIR__ . '/../enums/profile.enum.php';

use App\Models;
use App\Services;
use App\Enums;

// Affichage du tableau de bord
function dashboard() {
    global $session_services, $model;
    
    $session_services['start_session']();
    
    // Vérification si l'utilisateur est connecté
    if (!$session_services['is_logged_in']()) {
        redirect('?page=login');
        return;
    }
    
    // Récupération des données de l'utilisateur
    $user = $session_services['get_current_user']();
    
    // Rendu de la page tableau de bord
    render('admin.layout.view.php', 'dashboard/dashboard.php', [
        'user' => $user,
        'active_menu' => 'dashboard'
    ]);
}