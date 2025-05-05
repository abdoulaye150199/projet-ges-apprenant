<?php

namespace App\Models;

require_once __DIR__ . '/base.model.php';

/**
 * Fonctions pour la gestion des promotions
 */
$promotion_model = [
    /**
     * Récupère toutes les promotions
     * @return array Liste des promotions
     */
    'get_all_promotions' => function () use (&$base_model) {
        $data = $base_model['read_data']();
        return $data['promotions'] ?? [];
    },
    
    /**
     * Récupère une promotion par son ID
     * @param int $id ID de la promotion
     * @return array|null Données de la promotion ou null si non trouvée
     */
    'get_promotion_by_id' => function ($id) use (&$base_model) {
        $data = $base_model['read_data']();
        
        $filtered_promotions = array_filter($data['promotions'] ?? [], function ($promotion) use ($id) {
            return $promotion['id'] === $id;
        });
        
        return !empty($filtered_promotions) ? reset($filtered_promotions) : null;
    },
    
    /**
     * Vérifie si un nom de promotion existe déjà
     * @param string $name Nom de la promotion
     * @return bool True si le nom existe, false sinon
     */
    'promotion_name_exists' => function(string $name) use (&$base_model): bool {
        $data = $base_model['read_data']();
        
        foreach ($data['promotions'] as $promotion) {
            if (strtolower($promotion['name']) === strtolower($name)) {
                return true;
            }
        }
        
        return false;
    },
    
    /**
     * Crée une nouvelle promotion
     * @param array $promotion_data Données de la promotion
     * @return bool Succès de l'opération
     */
    'create_promotion' => function(array $promotion_data) use (&$base_model) {
        $data = $base_model['read_data']();
        
        // Générer un nouvel ID
        $max_id = 0;
        foreach ($data['promotions'] as $promotion) {
            $max_id = max($max_id, (int)$promotion['id']);
        }
        
        $promotion_data['id'] = $max_id + 1;
        $promotion_data['status'] = 'inactive'; // Statut inactif par défaut
        
        // Ajouter la promotion
        $data['promotions'][] = $promotion_data;
        
        // Sauvegarder les données
        return $base_model['write_data']($data);
    },
    
    /**
     * Met à jour une promotion
     * @param int $id ID de la promotion
     * @param array $promotion_data Nouvelles données
     * @return array|null Promotion mise à jour ou null si échec
     */
    'update_promotion' => function ($id, $promotion_data) use (&$base_model) {
        $data = $base_model['read_data']();
        
        // Trouver l'index de la promotion
        $promotion_indices = array_keys(array_filter($data['promotions'], function($promotion) use ($id) {
            return $promotion['id'] === $id;
        }));
        
        if (empty($promotion_indices)) {
            return false;
        }
        
        $promotion_index = reset($promotion_indices);
        
        // Mettre à jour les données de la promotion
        $data['promotions'][$promotion_index] = array_merge(
            $data['promotions'][$promotion_index],
            $promotion_data
        );
        
        if ($base_model['write_data']($data)) {
            return $data['promotions'][$promotion_index];
        }
        
        return null;
    },
    
    /**
     * Change le statut d'une promotion
     * @param int $promotion_id ID de la promotion
     * @return array|null Promotion mise à jour ou null si échec
     */
    'toggle_promotion_status' => function(int $promotion_id) use (&$base_model) {
        $data = $base_model['read_data']();
        
        // Trouver la promotion à modifier
        $target_promotion = null;
        $target_index = null;
        
        foreach ($data['promotions'] as $index => $promotion) {
            if ((int)$promotion['id'] === $promotion_id) {
                $target_promotion = $promotion;
                $target_index = $index;
                break;
            }
        }
        
        if ($target_index === null) {
            return false;
        }
        
        // Si la promotion est inactive
        if ($target_promotion['status'] === 'inactive') {
            // Désactiver toutes les promotions
            $data['promotions'] = array_map(function($p) {
                $p['status'] = 'inactive';
                return $p;
            }, $data['promotions']);
            
            // Activer la promotion ciblée
            $data['promotions'][$target_index]['status'] = 'active';
        } else {
            // Si la promotion est active, la désactiver
            $data['promotions'][$target_index]['status'] = 'inactive';
        }
        
        // Sauvegarder les modifications
        if ($base_model['write_data']($data)) {
            return $data['promotions'][$target_index];
        }
        
        return null;
    },
    
    /**
     * Met à jour le statut d'une promotion
     * @param int $promotion_id ID de la promotion
     * @param string $status Nouveau statut
     * @return bool Succès de l'opération
     */
    'update_promotion_status' => function(int $promotion_id, string $status) use (&$base_model) {
        $data = $base_model['read_data']();
        
        foreach ($data['promotions'] as &$promotion) {
            if ((int)$promotion['id'] === $promotion_id) {
                $promotion['status'] = $status;
                return $base_model['write_data']($data);
            }
        }
        
        return false;
    },
    
    /**
     * Recherche des promotions par terme
     * @param string $search_term Terme de recherche
     * @return array Promotions correspondantes
     */
    'search_promotions' => function($search_term) use (&$promotion_model) {
        $promotions = $promotion_model['get_all_promotions']();
        
        if (empty($search_term)) {
            return $promotions;
        }
        
        return array_values(array_filter($promotions, function($promotion) use ($search_term) {
            return stripos($promotion['name'], $search_term) !== false;
        }));
    },
    
    /**
     * Récupère la promotion active courante
     * @return array|null Promotion active ou null si aucune n'est active
     */
    'get_current_promotion' => function () use (&$base_model) {
        $data = $base_model['read_data']();
        
        $active_promotions = array_filter($data['promotions'] ?? [], function ($promotion) {
            return $promotion['status'] === 'active';
        });
        
        if (empty($active_promotions)) {
            return null;
        }
        
        // Trier par date de début (la plus récente d'abord)
        usort($active_promotions, function ($a, $b) {
            return strtotime($b['date_debut']) - strtotime($a['date_debut']);
        });
        
        return reset($active_promotions);
    },
    
    /**
     * Récupère les statistiques des promotions
     * @return array Statistiques
     */
    'get_promotions_stats' => function () use (&$promotion_model, &$base_model) {
        $data = $base_model['read_data']();
        
        // Nombre total de promotions
        $total_promotions = count($data['promotions'] ?? []);
        
        // Nombre de promotions actives
        $active_promotions = count(array_filter($data['promotions'] ?? [], function ($promotion) {
            return $promotion['status'] === 'active';
        }));
        
        // Récupérer la promotion courante
        $current_promotion = $promotion_model['get_current_promotion']();
        
        // Nombre d'apprenants dans la promotion courante
        $current_promotion_apprenants = 0;
        if ($current_promotion) {
            $current_promotion_apprenants = count(array_filter($data['apprenants'] ?? [], function ($apprenant) use ($current_promotion) {
                return $apprenant['promotion_id'] === $current_promotion['id'];
            }));
        }
        
        // Nombre de référentiels dans la promotion courante
        $current_promotion_referentiels = 0;
        if ($current_promotion) {
            $current_promotion_referentiels = count($current_promotion['referentiels'] ?? []);
        }
        
        return [
            'total_promotions' => $total_promotions,
            'active_promotions' => $active_promotions,
            'current_promotion_apprenants' => $current_promotion_apprenants,
            'current_promotion_referentiels' => $current_promotion_referentiels
        ];
    }
];