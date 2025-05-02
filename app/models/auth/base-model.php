<?php
namespace App\Models\Auth;

return [
    'authenticate_admin' => function($login, $password) use ($model) {
        $data = $model['read_data']();
        $users = $data['users'] ?? [];
        
        // Recherche de l'utilisateur par email
        $user = $model['array_find']($users, function($u) use ($login) {
            return $u['email'] === $login;
        });

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ];
        }

        // Pour l'administrateur dans data.json, le mot de passe n'est pas hashé
        if ($user['password'] === $password) {
            return [
                'success' => true,
                'data' => $user
            ];
        }

        return [
            'success' => false,
            'message' => 'Mot de passe incorrect'
        ];
    },

    'authenticate_apprenant' => function($login, $password) use ($model) {
        $data = $model['read_data']();
        $apprenants = $data['apprenants'] ?? [];
        
        // Recherche par matricule ou email
        $apprenant = $model['array_find']($apprenants, function($a) use ($login) {
            return $a['matricule'] === $login || $a['email'] === $login;
        });

        if (!$apprenant) {
            return [
                'success' => false,
                'message' => 'Identifiants invalides'
            ];
        }

        // Vérifier le mot de passe
        if (password_verify($password, $apprenant['password'])) {
            return [
                'success' => true,
                'data' => [
                    'id' => $apprenant['id'],
                    'matricule' => $apprenant['matricule'],
                    'nom' => $apprenant['nom'],
                    'prenom' => $apprenant['prenom'],
                    'email' => $apprenant['email'],
                    'type' => 'apprenant',
                    'first_login' => $apprenant['first_login'] ?? true
                ]
            ];
        }

        return [
            'success' => false,
            'message' => 'Identifiants invalides'
        ];
    }
];