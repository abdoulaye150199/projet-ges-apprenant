<?php

namespace App\Models;

require_once __DIR__ . '/../enums/path.enum.php';

use App\Enums;
use \Exception;

/**
 * Fonctions de base pour la manipulation des données
 */
$base_model = [
    /**
     * Lit les données depuis le fichier JSON
     * @return array Données du fichier
     */
    'read_data' => function () {
        if (!file_exists(Enums\DATA_PATH)) {
            return [
                'users' => [],
                'promotions' => [],
                'referentiels' => [],
                'apprenants' => []
            ];
        }
        
        $json_data = file_get_contents(Enums\DATA_PATH);
        $data = json_decode($json_data, true);
        
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return [
                'users' => [],
                'promotions' => [],
                'referentiels' => [],
                'apprenants' => []
            ];
        }
        
        return $data;
    },
    
    /**
     * Écrit les données dans le fichier JSON
     * @param array $data Données à écrire
     * @return bool Succès de l'opération
     */
    'write_data' => function ($data) {
        $data_dir = dirname(Enums\DATA_PATH);
        if (!is_dir($data_dir)) {
            if (!mkdir($data_dir, 0777, true)) {
                throw new Exception("Impossible de créer le dossier data");
            }
        }
        
        if (!is_writable($data_dir)) {
            throw new Exception("Le dossier data n'est pas accessible en écriture");
        }
        
        if (file_exists(Enums\DATA_PATH) && !is_writable(Enums\DATA_PATH)) {
            throw new Exception("Le fichier data.json n'est pas accessible en écriture");
        }
        
        $json_data = json_encode($data, JSON_PRETTY_PRINT);
        if (file_put_contents(Enums\DATA_PATH, $json_data) === false) {
            throw new Exception("Erreur lors de l'écriture dans data.json");
        }
        return true;
    },
    
    /**
     * Génère un identifiant unique
     * @return string Identifiant unique
     */
    'generate_id' => function () {
        return uniqid();
    }
];