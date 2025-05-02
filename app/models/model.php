<?php
namespace App\Models;

// Base model functions
$model = [
    'read_data' => function() {
        $json_file = __DIR__ . '/../data/data.json';
        if (!file_exists($json_file)) {
            return [
                'users' => [],
                'promotions' => [],
                'referentiels' => [],
                'apprenants' => []
            ];
        }
        return json_decode(file_get_contents($json_file), true);
    },

    'write_data' => function($data) {
        $json_file = __DIR__ . '/../data/data.json';
        return file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT));
    },

    'array_find' => function($array, $callback) {
        if (!is_array($array)) return null;
        foreach ($array as $element) {
            if ($callback($element)) {
                return $element;
            }
        }
        return null;
    },
    
    'array_findindex' => function($array, $callback) {
        if (!is_array($array)) return -1;
        foreach ($array as $index => $element) {
            if ($callback($element)) {
                return $index;
            }
        }
        return -1;
    }
];

// Load base promotion functions first
$promotion_base = require __DIR__ . '/promotion/base-model.php';
$model = array_merge($model, $promotion_base);

// Load other base functions
$model['get_apprenant_by_id'] = function($id) use ($model) {
    $data = $model['read_data']();
    $apprenants = $data['apprenants'] ?? [];
    return $model['array_find']($apprenants, fn($a) => $a['id'] === $id);
};

// Load models in correct dependency order
$model = array_merge(
    $model,
    require __DIR__ . '/auth/base-model.php',       // Load auth base first
    require __DIR__ . '/apprenant/base-model.php',  // Then other base models
    require __DIR__ . '/promotion/base-model.php',
    require __DIR__ . '/referentiel/base-model.php',
    require __DIR__ . '/apprenant/addapp-model.php',  // Ensuite les modèles spécifiques
    require __DIR__ . '/promotion/lisprom-model.php',
    require __DIR__ . '/promotion/addprom-model.php',
    require __DIR__ . '/referentiel/lisref-model.php',
    require __DIR__ . '/referentiel/ref-add.model.php',
    require __DIR__ . '/referentiel/assign-model.php',
    require __DIR__ . '/auth/auth.model.php',
    require __DIR__ . '/auth/login.model.php',
    require __DIR__ . '/auth/change.model.php'
);

return $model;
?>