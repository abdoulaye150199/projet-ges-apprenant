<?php

namespace App\Services;

// Regroupement des fonctions liées aux sessions
$session_services = [
    'start_session' => function () {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    },
    
    'set_session' => function ($key, $value) {
        $_SESSION[$key] = $value;
    },
    
    'get_session' => function ($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    },
    
    'remove_session' => function ($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    },
    
    'destroy_session' => function () {
        session_destroy();
    },
    
    'is_logged_in' => function () {
        return isset($_SESSION['user']);
    },
    
    'get_current_user' => function () {
        return isset($_SESSION['user']) ? $_SESSION['user'] : null;
    },
    
    'set_flash_message' => function ($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    },
    
    'get_flash_message' => function () {
        $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
        if (isset($_SESSION['flash'])) {
            unset($_SESSION['flash']);
        }
        return $flash;
    },
    
    'is_session_started' => function() {
        return session_status() === PHP_SESSION_ACTIVE;
    },
];