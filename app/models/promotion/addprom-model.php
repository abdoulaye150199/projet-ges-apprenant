<?php
namespace App\Models\Promotion;

return [
    'create_promotion' => function($promotion_data) use ($model) {
        $data = $model['read_data']();
        
        // Générer un ID unique pour la promotion
        $promotion_data['id'] = $model['generate_unique_id']('PROM_');
        $promotion_data['created_at'] = date('Y-m-d H:i:s');
        
        // Initialiser les tableaux si nécessaire
        $promotion_data['apprenants'] = $promotion_data['apprenants'] ?? [];
        $promotion_data['referentiels'] = $promotion_data['referentiels'] ?? [];
        
        // S'assurer que le statut est défini
        $promotion_data['status'] = $promotion_data['status'] ?? 'inactive';
        
        // Ajouter la nouvelle promotion
        $data['promotions'][] = $promotion_data;
        
        // Sauvegarder les données
        return $model['write_data']($data);
    },

    'get_current_promotion' => function() use ($model) {
        $data = $model['read_data']();
        return $model['array_find']($data['promotions'], 
            fn($p) => $p['status'] === 'active'
        );
    },

    'update_promotion_status' => function($promotion_id, $new_status) use ($model) {
        $data = $model['read_data']();
        $index = $model['array_findindex']($data['promotions'], 
            fn($p) => $p['id'] === $promotion_id
        );
        
        if ($index !== -1) {
            $data['promotions'][$index]['status'] = $new_status;
            $data['promotions'][$index]['updated_at'] = date('Y-m-d H:i:s');
            return $model['write_data']($data);
        }
        return false;
    }
];