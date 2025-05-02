<?php
namespace App\Models\Promotion;

return [
    'get_all_promotions' => function() use ($model) {
        $data = $model['read_data']();
        $promotions = $data['promotions'] ?? [];
        
        // Sort promotions by creation date
        usort($promotions, function($a, $b) {
            // Convert dates to timestamps for comparison
            $dateA = is_string($a['created_at'] ?? null) ? strtotime($a['created_at']) : 0;
            $dateB = is_string($b['created_at'] ?? null) ? strtotime($b['created_at']) : 0;
            
            // If dates are equal, maintain original order
            if ($dateA === $dateB) {
                return 0;
            }
            
            return $dateB <=> $dateA;
        });
        
        return $promotions;
    },

    'get_promotion_by_id' => function($id) use ($model) {
        $data = $model['read_data']();
        $promotions = $data['promotions'] ?? [];
        return $model['array_find']($promotions, fn($p) => $p['id'] === $id);
    },

    'get_active_promotions' => function() use ($model) {
        $data = $model['read_data']();
        $promotions = $data['promotions'] ?? [];
        return array_filter($promotions, fn($p) => ($p['status'] ?? '') === 'active');
    }
];