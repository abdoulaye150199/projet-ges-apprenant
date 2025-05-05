<?php
require_once __DIR__ . '/../app/helpers/functions.php';
// Afficher les erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/route/route.web.php';
require_once __DIR__ . '/../app/services/session.service.php';

// Démarrer la session et router vers la page demandée
App\Route\handle_request();