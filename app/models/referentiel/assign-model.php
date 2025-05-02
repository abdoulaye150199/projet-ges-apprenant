<?php
namespace App\Models\Referentiel;

return [
    'assign_referentiels_to_promotion' => function($promotion_id, $referentiel_ids) use ($model) {
        $data = $model['read_data']();
        $index = $model['array_findindex']($data['promotions'], 
            fn($p) => $p['id'] === $promotion_id
        );
        
        if ($index !== -1) {
            $data['promotions'][$index]['referentiels'] = $referentiel_ids;
            $data['promotions'][$index]['updated_at'] = date('Y-m-d H:i:s');
            return $model['write_data']($data);
        }
        return false;
    },

    'unassign_referentiel_from_promotion' => function($promotion_id, $referentiel_id) use ($model) {
        $data = $model['read_data']();
        $index = $model['array_findindex']($data['promotions'], 
            fn($p) => $p['id'] === $promotion_id
        );
        
        if ($index !== -1) {
            $referentiels = $data['promotions'][$index]['referentiels'] ?? [];
            $referentiels = array_filter($referentiels, fn($ref) => $ref !== $referentiel_id);
            $data['promotions'][$index]['referentiels'] = array_values($referentiels);
            $data['promotions'][$index]['updated_at'] = date('Y-m-d H:i:s');
            return $model['write_data']($data);
        }
        return false;
    }
];