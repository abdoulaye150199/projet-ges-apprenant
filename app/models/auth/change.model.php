<?php
namespace App\Models\Auth;

return [
    'change_password' => function($user_id, $old_password, $new_password) use ($model) {
        $data = $model['read_data']();
        $index = $model['array_findindex']($data['users'], fn($u) => $u['id'] === $user_id);
        
        if ($index === -1) {
            return ['success' => false, 'message' => 'Utilisateur non trouvé'];
        }

        if (!password_verify($old_password, $data['users'][$index]['password'])) {
            return ['success' => false, 'message' => 'Ancien mot de passe incorrect'];
        }

        $data['users'][$index]['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        $data['users'][$index]['updated_at'] = date('Y-m-d H:i:s');
        
        return $model['write_data']($data) 
            ? ['success' => true] 
            : ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
    }
];