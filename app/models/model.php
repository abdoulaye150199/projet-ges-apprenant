<?php

namespace App\Models;

// Chargement des modèles individuels
require_once __DIR__ . '/base.model.php';
require_once __DIR__ . '/user.model.php';
require_once __DIR__ . '/promotion.model.php';
require_once __DIR__ . '/referentiel.model.php';
require_once __DIR__ . '/apprenant.model.php';

/**
 * Modèle principal qui regroupe tous les modèles individuels
 * 
 * Ce fichier est le point d'entrée pour l'accès aux données
 * Il expose une variable $model qui contient toutes les fonctions des différents modèles
 */

// Fusion de tous les modèles en un seul tableau de fonctions
$model = array_merge(
    $base_model,
    $user_model,
    $promotion_model,
    $referentiel_model,
    $apprenant_model
);

// Ajout de fonctions qui dépendent de plusieurs modèles si nécessaire

// Exemple de fonction qui utiliserait plusieurs modèles:
// $model['stats_dashboard'] = function() use (&$promotion_model, &$apprenant_model, &$referentiel_model) {
//     // Code utilisant plusieurs modèles
// };