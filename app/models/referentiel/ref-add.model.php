<?php
namespace App\Models\Referentiel;

return [
    'create_referentiel' => function($referentiel_data) use ($model) {
        $data = $model['read_data']();
        
        // Générer un ID unique pour le référentiel
        $referentiel_data['id'] = $model['generate_unique_id']('REF_');
        $referentiel_data['created_at'] = date('Y-m-d H:i:s');
        
        // Valider les données requises
        $required_fields = ['name', 'description'];
        foreach ($required_fields as $field) {
            if (empty($referentiel_data[$field])) {
                return [
                    'success' => false,
                    'message' => "Le champ {$field} est requis"
                ];
            }
        }
        
        // Vérifier si le nom existe déjà en utilisant la fonction du modèle principal
        if ($model['get_referentiel_by_name']($referentiel_data['name'])) {
            return [
                'success' => false,
                'message' => 'Un référentiel avec ce nom existe déjà'
            ];
        }
        
        // Ajouter le nouveau référentiel
        $data['referentiels'][] = $referentiel_data;
        
        // Sauvegarder et retourner le résultat
        if ($model['write_data']($data)) {
            return [
                'success' => true,
                'referentiel' => $referentiel_data
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Erreur lors de la sauvegarde du référentiel'
        ];
    }
];