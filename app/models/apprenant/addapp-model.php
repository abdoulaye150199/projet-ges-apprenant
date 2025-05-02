<?php
namespace App\Models\Apprenant;

return [
    // Base CRUD functions
    'get_all_apprenants' => function() use ($model) {
        $data = $model['read_data']();
        return $data['apprenants'] ?? [];
    },

    'get_apprenant_by_id' => function($id) use ($model) {
        $data = $model['read_data']();
        $apprenants = $data['apprenants'] ?? [];
        return $model['array_find']($apprenants, fn($a) => $a['id'] === $id);
    },

    'update_apprenant' => function($id, $updates) use ($model) {
        $data = $model['read_data']();
        $index = $model['array_findindex']($data['apprenants'], fn($a) => $a['id'] === $id);
        
        if ($index !== -1) {
            $data['apprenants'][$index] = array_merge(
                $data['apprenants'][$index],
                $updates,
                ['updated_at' => date('Y-m-d H:i:s')]
            );
            return $model['write_data']($data);
        }
        return false;
    },

    'delete_apprenant' => function($id) use ($model) {
        $data = $model['read_data']();
        $index = $model['array_findindex']($data['apprenants'], fn($a) => $a['id'] === $id);
        
        if ($index !== -1) {
            array_splice($data['apprenants'], $index, 1);
            return $model['write_data']($data);
        }
        return false;
    },

    'add_apprenant' => function($apprenant_data) use ($model) {
        try {
            $data = $model['read_data']();
            
            // Générer un ID unique
            $apprenant_data['id'] = uniqid();
            
            // Générer le matricule
            $apprenant_data['matricule'] = 'ODC-' . date('Y') . '-' . str_pad(count($data['apprenants']) + 1, 4, '0', STR_PAD_LEFT);
            
            // Générer le mot de passe temporaire
            $plain_password = 'Sonatel@' . date('Y'); // ou utilisez password_generate()
            $apprenant_data['password'] = password_hash($plain_password, PASSWORD_DEFAULT);
            
            // Ajouter les champs supplémentaires
            $apprenant_data['created_at'] = date('Y-m-d H:i:s');
            $apprenant_data['status'] = 'actif';
            $apprenant_data['first_login'] = true; // Pour forcer le changement de mot de passe
            
            // Sauvegarder l'apprenant
            $data['apprenants'][] = $apprenant_data;
            $save_result = $model['write_data']($data);
            
            if (!$save_result) {
                return [
                    'success' => false,
                    'message' => "Erreur lors de l'enregistrement"
                ];
            }
            
            // Envoyer l'email avec les identifiants
            $mail_sent = \App\Services\send_apprenant_credentials($apprenant_data, $plain_password);
            
            return [
                'success' => true,
                'apprenant' => $apprenant_data,
                'plain_password' => $plain_password,
                'mail_sent' => $mail_sent
            ];
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [
                'success' => false,
                'message' => "Une erreur est survenue"
            ];
        }
    },

    'get_apprenant_by_email' => function($email) use ($model) {
        $data = $model['read_data']();
        return $model['array_find']($data['apprenants'], 
            fn($a) => $a['email'] === $email
        );
    },

    'get_apprenant_by_matricule' => function($matricule) use ($model) {
        $data = $model['read_data']();
        return $model['array_find']($data['apprenants'], 
            fn($a) => $a['matricule'] === $matricule
        );
    },

    'check_apprenant_exists' => function($email, $matricule) use ($model) {
        $data = $model['read_data']();
        $apprenants = $data['apprenants'] ?? [];
        
        return $model['array_find']($apprenants, function($apprenant) use ($email, $matricule) {
            // Check if email or matricule exists and convert to string if needed
            $apprenant_email = isset($apprenant['email']) ? (string)$apprenant['email'] : '';
            $email = (string)$email;
            
            return strtolower($apprenant_email) === strtolower($email) || 
                   ($apprenant['matricule'] ?? '') === $matricule;
        });
    },

    // Additional functions that depend on base CRUD
    'get_apprenant_modules' => function($apprenant_id) use ($model) {
        $apprenant = $model['get_apprenant_by_id']($apprenant_id);
        
        if (!$apprenant || !isset($apprenant['promotion_id'])) {
            return [];
        }
        
        $promotion = $model['get_promotion_by_id']($apprenant['promotion_id']);
        if (!$promotion || empty($promotion['referentiels'])) {
            return [];
        }
        
        $modules = [];
        foreach ($promotion['referentiels'] as $ref_id) {
            $referentiel = $model['get_referentiel_by_id']($ref_id);
            if ($referentiel && !empty($referentiel['modules'])) {
                foreach ($referentiel['modules'] as $module) {
                    $module['status'] = calculate_module_status($module);
                    $modules[] = $module;
                }
            }
        }
        
        return $modules;
    }
];

// Helper function
function calculate_module_status($module) {
    $now = time();
    $start_date = strtotime($module['date_debut'] ?? '');
    $end_date = strtotime($module['date_fin'] ?? '');
    
    if (!$start_date || !$end_date) {
        return 'non_planifie';
    }
    
    if ($now < $start_date) {
        return 'a_venir';
    } elseif ($now > $end_date) {
        return 'termine';
    }
    
    return 'en_cours';
}