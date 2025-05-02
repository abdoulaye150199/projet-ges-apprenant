<?php
namespace App\Models\Apprenant;

return [
    'get_apprenant_by_id' => function($id) use ($model) {
        $data = $model['read_data']();
        $apprenants = $data['apprenants'] ?? [];
        return $model['array_find']($apprenants, fn($a) => $a['id'] === $id);
    },

    'get_all_apprenants' => function() use ($model) {
        $data = $model['read_data']();
        return $data['apprenants'] ?? [];
    }
];