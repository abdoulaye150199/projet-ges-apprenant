<?php
namespace App\Models\Auth;

return [
    'authenticate' => function($login, $password) use ($model) {
        $data = $model['read_data']();
        $users = $data['users'] ?? [];

        // Find user by login (email)
        $user = $model['array_find']($users, function($u) use ($login) {
            return $u['email'] === $login;
        });

        // If user not found
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Identifiants incorrects'
            ];
        }

        // Verify password (in a real app, use password_verify)
        if ($user['password'] !== $password) {
            return [
                'success' => false,
                'message' => 'Identifiants incorrects'
            ];
        }

        // Authentication successful
        return [
            'success' => true,
            'user' => $user
        ];
    },

    'authenticate_apprenant' => function($login, $password) use ($model) {
        $data = $model['read_data']();
        $apprenants = $data['apprenants'] ?? [];

        // Search by email or matricule
        $apprenant = $model['array_find']($apprenants, function($a) use ($login) {
            return $a['email'] === $login || $a['matricule'] === $login;
        });

        if (!$apprenant) {
            return [
                'success' => false,
                'message' => 'Identifiants incorrects'
            ];
        }

        // Verify password
        if ($apprenant['password'] !== $password) {
            return [
                'success' => false,
                'message' => 'Mot de passe incorrect'
            ];
        }

        // Add type for middleware check
        $apprenant['type'] = 'apprenant';

        return [
            'success' => true,
            'data' => $apprenant
        ];
    }
];