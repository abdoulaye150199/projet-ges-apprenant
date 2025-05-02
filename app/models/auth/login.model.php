<?php
namespace App\Models\Auth;

return [
    'authenticate_user' => function($email, $password) use ($model) {
        $data = $model['read_data']();
        $user = $model['array_find']($data['users'], fn($u) => $u['email'] === $email);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Identifiants incorrects'];
        }

        return ['success' => true, 'user' => $user];
    }
];