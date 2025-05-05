<?php

namespace App\Models;

require_once __DIR__ . '/base.model.php';

/**
 * Fonctions pour la gestion des utilisateurs
 */
$user_model = [
    /**
     * Authentifie un utilisateur
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe de l'utilisateur
     * @return array|null Données de l'utilisateur ou null si échec
     */
    'authenticate' => function ($email, $password) use (&$base_model) {
        $data = $base_model['read_data']();
        
        $filtered_users = array_filter($data['users'], function ($user) use ($email, $password) {
            return $user['email'] === $email && $user['password'] === $password;
        });
        
        if (empty($filtered_users)) {
            return null;
        }
        
        // Récupérer le premier utilisateur qui correspond
        return reset($filtered_users);
    },
    
    /**
     * Récupère un utilisateur par son email
     * @param string $email Email de l'utilisateur
     * @return array|null Données de l'utilisateur ou null si non trouvé
     */
    'get_user_by_email' => function ($email) use (&$base_model) {
        $data = $base_model['read_data']();
        
        $filtered_users = array_filter($data['users'], function ($user) use ($email) {
            return $user['email'] === $email;
        });
        
        if (empty($filtered_users)) {
            return null;
        }
        
        return reset($filtered_users);
    },
    
    /**
     * Récupère un utilisateur par son ID
     * @param string $user_id ID de l'utilisateur
     * @return array|null Données de l'utilisateur ou null si non trouvé
     */
    'get_user_by_id' => function ($user_id) use (&$base_model) {
        $data = $base_model['read_data']();
        
        $filtered_users = array_filter($data['users'], function ($user) use ($user_id) {
            return $user['id'] === $user_id;
        });
        
        if (empty($filtered_users)) {
            return null;
        }
        
        return reset($filtered_users);
    },
    
    /**
     * Change le mot de passe d'un utilisateur
     * @param string $user_id ID de l'utilisateur
     * @param string $new_password Nouveau mot de passe
     * @return bool Succès de l'opération
     */
    'change_password' => function ($user_id, $new_password) use (&$base_model) {
        $data = $base_model['read_data']();
        
        $user_indices = array_keys(array_filter($data['users'], function($user) use ($user_id) {
            return $user['id'] === $user_id;
        }));
        
        if (empty($user_indices)) {
            return false;
        }
        
        $user_index = reset($user_indices);
        
        // Mettre à jour le mot de passe
        $data['users'][$user_index]['password'] = $new_password;
        
        // Sauvegarder les modifications
        return $base_model['write_data']($data);
    }
];