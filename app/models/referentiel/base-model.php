<?php
namespace App\Models\Referentiel;

return [
    'get_all_referentiels' => function() use ($model) {
        $data = $model['read_data']();
        return $data['referentiels'] ?? [];
    },

    'get_referentiel_by_id' => function($id) use ($model) {
        $data = $model['read_data']();
        $referentiels = $data['referentiels'] ?? [];
        return $model['array_find']($referentiels, fn($r) => $r['id'] === $id);
    },

    'get_referentiel_by_name' => function($name) use ($model) {
        $data = $model['read_data']();
        $referentiels = $data['referentiels'] ?? [];
        return $model['array_find']($referentiels, function($r) use ($name) {
            return strtolower($r['name']) === strtolower($name);
        });
    },

    'get_referentiels_by_promotion' => function($promotion_id) use ($model) {
        $data = $model['read_data']();
        $promotion = $model['array_find']($data['promotions'], 
            fn($p) => $p['id'] === $promotion_id
        );
        
        if (!$promotion || !isset($promotion['referentiels'])) {
            return [];
        }
        
        $referentiels = $data['referentiels'] ?? [];
        return array_filter($referentiels, 
            fn($ref) => in_array($ref['id'], $promotion['referentiels'])
        );
    }
];