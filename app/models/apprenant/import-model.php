<?php
namespace App\Models\Apprenant;

return [
    'import_apprenants' => function($apprenants_data) use ($model) {
        $data = $model['read_data']();
        $success_count = 0;
        $errors = [];
        
        foreach ($apprenants_data as $apprenant) {
            $apprenant['id'] = $model['generate_unique_id']('APP_');
            $apprenant['created_at'] = date('Y-m-d H:i:s');
            $apprenant['status'] = 'pending';
            
            try {
                $data['apprenants'][] = $apprenant;
                $success_count++;
            } catch (\Exception $e) {
                $errors[] = "Erreur pour {$apprenant['prenom']} {$apprenant['nom']}: {$e->getMessage()}";
            }
        }
        
        if ($model['write_data']($data)) {
            return [
                'success' => true,
                'imported' => $success_count,
                'errors' => $errors
            ];
        }
        
        return ['success' => false, 'message' => 'Erreur lors de l\'import'];
    }
];