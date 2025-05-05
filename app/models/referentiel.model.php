<?php

namespace App\Models;

require_once __DIR__ . '/base.model.php';

/**
 * Fonctions pour la gestion des référentiels
 */
$referentiel_model = [
    /**
     * Récupère tous les référentiels
     * @return array Liste des référentiels
     */
    'get_all_referentiels' => function () use (&$base_model) {
        $data = $base_model['read_data']();
        return $data['referentiels'] ?? [];
    },
    
    /**
     * Récupère un référentiel par son ID
     * @param string $id ID du référentiel
     * @return array|null Référentiel ou null si non trouvé
     */
    'get_referentiel_by_id' => function ($id) use (&$base_model) {
        $data = $base_model['read_data']();
        
        $filtered_referentiels = array_filter($data['referentiels'] ?? [], function ($referentiel) use ($id) {
            return $referentiel['id'] === $id;
        });
        
        return !empty($filtered_referentiels) ? reset($filtered_referentiels) : null;
    },
    
    /**
     * Vérifie si un nom de référentiel existe déjà
     * @param string $name Nom du référentiel
     * @param string|null $exclude_id ID à exclure (pour mise à jour)
     * @return bool True si le nom existe, false sinon
     */
    'referentiel_name_exists' => function ($name, $exclude_id = null) use (&$base_model) {
        $data = $base_model['read_data']();
        
        $filtered_referentiels = array_filter($data['referentiels'] ?? [], function ($referentiel) use ($name, $exclude_id) {
            return strtolower($referentiel['name']) === strtolower($name) && ($exclude_id === null || $referentiel['id'] !== $exclude_id);
        });
        
        return !empty($filtered_referentiels);
    },
    
    /**
     * Crée un nouveau référentiel
     * @param array $referentiel_data Données du référentiel
     * @return bool Succès de l'opération
     */
    'create_referentiel' => function ($referentiel_data) use (&$base_model) {
        $data = $base_model['read_data']();
        
        // Générer un ID unique
        $referentiel_data['id'] = uniqid();
        
        // Ajouter le référentiel à la liste
        $data['referentiels'][] = $referentiel_data;
        
        // Sauvegarder les modifications
        return $base_model['write_data']($data);
    },
    
    /**
     * Récupère les référentiels d'une promotion
     * @param int $promotion_id ID de la promotion
     * @return array Liste des référentiels
     */
    'get_referentiels_by_promotion' => function($promotion_id) use (&$base_model) {
        $data = $base_model['read_data']();
        
        // Trouver la promotion
        $promotion = null;
        foreach ($data['promotions'] as $p) {
            if ($p['id'] == $promotion_id) {
                $promotion = $p;
                break;
            }
        }
        
        if (!$promotion || empty($promotion['referentiels'])) {
            return [];
        }
        
        // Récupérer les référentiels associés
        return array_filter($data['referentiels'], function($ref) use ($promotion) {
            return in_array($ref['id'], $promotion['referentiels']);
        });
    },
    
    /**
     * Assigne des référentiels à une promotion
     * @param int $promotion_id ID de la promotion
     * @param array $referentiel_ids Liste des IDs de référentiels
     * @return bool Succès de l'opération
     */
    'assign_referentiels_to_promotion' => function ($promotion_id, $referentiel_ids) use (&$base_model) {
        $data = $base_model['read_data']();
        
        // Trouver l'index de la promotion
        $promotion_indices = array_keys(array_filter($data['promotions'], function($promotion) use ($promotion_id) {
            return $promotion['id'] === $promotion_id;
        }));
        
        if (empty($promotion_indices)) {
            return false;
        }
        
        $promotion_index = reset($promotion_indices);
        
        // Ajouter les référentiels à la promotion
        if (!isset($data['promotions'][$promotion_index]['referentiels'])) {
            $data['promotions'][$promotion_index]['referentiels'] = [];
        }
        
        $data['promotions'][$promotion_index]['referentiels'] = array_unique(
            array_merge($data['promotions'][$promotion_index]['referentiels'], $referentiel_ids)
        );
        
        return $base_model['write_data']($data);
    },
    
    /**
     * Recherche de référentiels par terme
     * @param string $query Terme de recherche
     * @return array Référentiels correspondants
     */
    'search_referentiels' => function(string $query) use (&$referentiel_model) {
        $referentiels = $referentiel_model['get_all_referentiels']();
        if (empty($query)) {
            return $referentiels;
        }
        
        return array_filter($referentiels, function($ref) use ($query) {
            return stripos($ref['name'], $query) !== false || 
                   stripos($ref['description'], $query) !== false;
        });
    },
    
    /**
     * Récupère un référentiel par son nom
     * @param string $name Nom du référentiel
     * @return array|null Référentiel ou null si non trouvé
     */
    'get_referentiel_by_name' => function($name) use (&$base_model) {
        $data = $base_model['read_data']();
        foreach ($data['referentiels'] as $ref) {
            if (strtolower($ref['name']) === strtolower($name)) {
                return $ref;
            }
        }
        return null;
    },
    
    /**
     * Vérifie si un référentiel a des apprenants
     * @param int $promotion_id ID de la promotion
     * @param string $referentiel_id ID du référentiel
     * @return bool True si le référentiel a des apprenants
     */
    'referentiel_has_apprenants' => function ($promotion_id, $referentiel_id) use (&$base_model) {
        $data = $base_model['read_data']();
        
        // Trouver la promotion
        $promotion = null;
        foreach ($data['promotions'] as $p) {
            if ($p['id'] == $promotion_id) {
                $promotion = $p;
                break;
            }
        }
        
        if (!$promotion || empty($promotion['apprenants'])) {
            return false;
        }
        
        // Vérifier si des apprenants utilisent ce référentiel
        foreach ($promotion['apprenants'] as $apprenant) {
            if (isset($apprenant['referentiel_id']) && $apprenant['referentiel_id'] == $referentiel_id) {
                return true;
            }
        }
        
        return false;
    },
    
    /**
     * Désaffecte un référentiel d'une promotion
     * @param int $promotion_id ID de la promotion
     * @param string $referentiel_id ID du référentiel
     * @return bool Succès de l'opération
     */
    'unassign_referentiel_from_promotion' => function ($promotion_id, $referentiel_id) use (&$base_model) {
        $data = $base_model['read_data']();
        
        // Trouver l'index de la promotion
        $promotion_indices = array_keys(array_filter($data['promotions'], function($promotion) use ($promotion_id) {
            return $promotion['id'] == $promotion_id;
        }));
        
        if (empty($promotion_indices)) {
            return false;
        }
        
        $promotion_index = reset($promotion_indices);
        
        // Vérifier si la promotion a des référentiels
        if (!isset($data['promotions'][$promotion_index]['referentiels'])) {
            return false;
        }
        
        // Filtrer le référentiel à désaffecter
        $data['promotions'][$promotion_index]['referentiels'] = array_values(
            array_filter($data['promotions'][$promotion_index]['referentiels'], function($ref_id) use ($referentiel_id) {
                return $ref_id != $referentiel_id;
            })
        );
        
        return $base_model['write_data']($data);
    }
];