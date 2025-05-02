<?php
namespace App\Models\Promotion;

return [
    'get_promotion_by_id' => function($id) use ($model) {
        $data = $model['read_data']();
        $promotions = $data['promotions'] ?? [];
        return $model['array_find']($promotions, fn($p) => $p['id'] === $id);
    },

    'get_all_promotions' => function() use ($model) {
        $data = $model['read_data']();
        return $data['promotions'] ?? [];
    },

    'sort_promotions' => function($promotions, $sort_by = 'created_at', $direction = 'DESC') use ($model) {
        if (empty($promotions)) return [];
        
        usort($promotions, function($a, $b) use ($sort_by, $direction) {
            // Handle special case for status (active promotions first)
            if ($sort_by === 'status') {
                if ($a['status'] === 'active' && $b['status'] !== 'active') return -1;
                if ($a['status'] !== 'active' && $b['status'] === 'active') return 1;
            }
            
            // Handle dates
            if (in_array($sort_by, ['created_at', 'date_debut', 'date_fin'])) {
                $date_a = strtotime($a[$sort_by] ?? '0');
                $date_b = strtotime($b[$sort_by] ?? '0');
                return $direction === 'DESC' ? ($date_b - $date_a) : ($date_a - $date_b);
            }
            
            // Default string comparison
            $val_a = $a[$sort_by] ?? '';
            $val_b = $b[$sort_by] ?? '';
            $comparison = strcmp($val_a, $val_b);
            
            return $direction === 'DESC' ? -$comparison : $comparison;
        });
        
        return $promotions;
    },

    'get_current_promotion' => function() use ($model) {
        $data = $model['read_data']();
        $promotions = $data['promotions'] ?? [];
        return $model['array_find']($promotions, fn($p) => $p['status'] === 'active');
    },

    'assign_referentiels_to_promotion' => function($promotion_id, $referentiel_ids) use ($model) {
        try {
            $data = $model['read_data']();
            $index = $model['array_findindex']($data['promotions'], fn($p) => $p['id'] === $promotion_id);
            
            if ($index === -1) return false;
            
            // S'assurer que le tableau des référentiels existe
            if (!isset($data['promotions'][$index]['referentiels'])) {
                $data['promotions'][$index]['referentiels'] = [];
            }
            
            // Mettre à jour la liste des référentiels en conservant les doublons
            $data['promotions'][$index]['referentiels'] = array_values(
                array_unique(
                    array_merge(
                        $data['promotions'][$index]['referentiels'],
                        $referentiel_ids
                    )
                )
            );
            
            // Sauvegarder les modifications
            return $model['write_data']($data);
            
        } catch (Exception $e) {
            return false;
        }
    },

    'get_promotion_referentiels' => function($promotion_id) use ($model) {
        $data = $model['read_data']();
        $promotion = $model['array_find']($data['promotions'], fn($p) => $p['id'] === $promotion_id);
        
        if (!$promotion || empty($promotion['referentiels'])) {
            return [];
        }
        
        // Récupérer les détails complets des référentiels
        $referentiels = $data['referentiels'] ?? [];
        return array_filter($referentiels, function($ref) use ($promotion) {
            return in_array($ref['id'], $promotion['referentiels']);
        });
    },

    'remove_referentiel_from_promotion' => function($promotion_id, $referentiel_id) use ($model) {
        $data = $model['read_data']();
        $index = $model['array_findindex']($data['promotions'], fn($p) => $p['id'] === $promotion_id);
        
        if ($index !== -1 && isset($data['promotions'][$index]['referentiels'])) {
            // Retirer le référentiel de la liste
            $data['promotions'][$index]['referentiels'] = array_filter(
                $data['promotions'][$index]['referentiels'],
                fn($ref_id) => $ref_id !== $referentiel_id
            );
            
            return $model['write_data']($data);
        }
        return false;
    },

    'get_referentiels_by_promotion' => function($promotion_id) use ($model) {
        $data = $model['read_data']();
        $promotion = $model['array_find']($data['promotions'], fn($p) => $p['id'] === $promotion_id);
        
        if (!$promotion || empty($promotion['referentiels'])) {
            return [];
        }
        
        $referentiels = $data['referentiels'] ?? [];
        return array_filter($referentiels, function($ref) use ($promotion) {
            return in_array($ref['id'], $promotion['referentiels']);
        });
    }
];