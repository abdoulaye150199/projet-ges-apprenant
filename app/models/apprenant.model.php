<?php

namespace App\Models;

require_once __DIR__ . '/base.model.php';

/**
 * Fonctions pour la gestion des apprenants
 */
$apprenant_model = [
    /**
     * Récupère tous les apprenants
     * @return array Liste des apprenants
     */
    'get_all_apprenants' => function () use (&$base_model) {
        $data = $base_model['read_data']();
        return $data['apprenants'] ?? [];
    },
    
    /**
     * Récupère les apprenants d'une promotion
     * @param int $promotion_id ID de la promotion
     * @return array Liste des apprenants
     */
    'get_apprenants_by_promotion' => function ($promotion_id) use (&$base_model) {
        $data = $base_model['read_data']();
        
        // Filtrer les apprenants par promotion
        $apprenants = array_filter($data['apprenants'] ?? [], function ($apprenant) use ($promotion_id) {
            return $apprenant['promotion_id'] === $promotion_id;
        });
        
        return array_values($apprenants);
    },
    
    /**
     * Récupère un apprenant par son ID
     * @param string $id ID de l'apprenant
     * @return array|null Apprenant ou null si non trouvé
     */
    'get_apprenant_by_id' => function ($id) use (&$base_model) {
        $data = $base_model['read_data']();
        
        // Filtrer les apprenants par ID
        $filtered_apprenants = array_filter($data['apprenants'] ?? [], function ($apprenant) use ($id) {
            return $apprenant['id'] === $id;
        });
        
        return !empty($filtered_apprenants) ? reset($filtered_apprenants) : null;
    },
    
    /**
     * Récupère un apprenant par son matricule
     * @param string $matricule Matricule de l'apprenant
     * @return array|null Apprenant ou null si non trouvé
     */
    'get_apprenant_by_matricule' => function ($matricule) use (&$base_model) {
        $data = $base_model['read_data']();
        
        // Filtrer les apprenants par matricule
        $filtered_apprenants = array_filter($data['apprenants'] ?? [], function ($apprenant) use ($matricule) {
            return $apprenant['matricule'] === $matricule;
        });
        
        return !empty($filtered_apprenants) ? reset($filtered_apprenants) : null;
    },
    
    /**
     * Génère un matricule unique
     * @return string Matricule généré
     */
    'generate_matricule' => function () use (&$base_model) {
        $data = $base_model['read_data']();
        $year = date('Y');
        $count = count($data['apprenants'] ?? []) + 1;
        
        return 'ODC-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    },
    
    /**
     * Authentifie un apprenant
     * @param string $login Email ou matricule
     * @param string $password Mot de passe
     * @return array Résultat avec succès et données ou message d'erreur
     */
    'authenticate_apprenant' => function($login, $password) use (&$base_model) {
        try {
            $data = $base_model['read_data']();
            $apprenants = $data['apprenants'] ?? [];
            
            foreach ($apprenants as $apprenant) {
                // Vérifier si le login correspond à l'email ou au matricule
                if ($apprenant['email'] === $login || $apprenant['matricule'] === $login) {
                    // Vérifier le mot de passe
                    if (password_verify($password, $apprenant['password'])) {
                        // Ne pas renvoyer le mot de passe
                        $apprenant_data = $apprenant;
                        unset($apprenant_data['password']);
                        return ['success' => true, 'data' => $apprenant_data];
                    }
                }
            }
            
            return ['success' => false, 'message' => 'Identifiants incorrects'];
            
        } catch (\Exception $e) {
            error_log('Erreur authentication: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de l\'authentification'];
        }
    },
    
    /**
     * Ajoute un nouvel apprenant
     * @param array $apprenant_data Données de l'apprenant
     * @return array Résultat avec succès, apprenant et état de l'envoi du mail
     */
    'add_apprenant' => function($apprenant_data) use (&$base_model) {
        try {
            $data = $base_model['read_data']();
            
            // Initialiser le tableau des apprenants s'il n'existe pas
            if (!isset($data['apprenants'])) {
                $data['apprenants'] = [];
            }
            
            // Générer un ID unique
            $apprenant_data['id'] = uniqid();
            
            // Générer un matricule unique
            $year = date('Y');
            $count = count($data['apprenants']) + 1;
            $apprenant_data['matricule'] = sprintf("ODC-%d-%04d", $year, $count);
            
            // Vérifier le referentiel_id
            if (!isset($apprenant_data['referentiel_id'])) {
                throw new \Exception("Le referentiel_id est requis");
            }
            
            // Générer un mot de passe temporaire
            $temp_password = bin2hex(random_bytes(4));
            $apprenant_data['password'] = password_hash($temp_password, PASSWORD_DEFAULT);
            
            // Ajouter les champs manquants
            $apprenant_data['created_at'] = date('Y-m-d H:i:s');
            $apprenant_data['status'] = 'actif';
            
            // Ajouter l'apprenant au tableau
            $data['apprenants'][] = $apprenant_data;
            
            // Sauvegarder dans le fichier JSON
            if ($base_model['write_data']($data)) {
                // Charger le service mail
                require_once __DIR__ . '/../services/mail.service.php';
                
                // Envoyer les identifiants par email
                $mail_result = \App\Services\send_apprenant_credentials($apprenant_data, $temp_password);
                
                error_log('Résultat envoi mail: ' . ($mail_result ? 'Succès' : 'Échec'));
                
                return [
                    'success' => true,
                    'apprenant' => $apprenant_data,
                    'mail_sent' => $mail_result
                ];
            }
            
            return ['success' => false, 'message' => 'Erreur lors de la sauvegarde'];
            
        } catch (\Exception $e) {
            error_log('Erreur add_apprenant: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    },
    
    /**
     * Met à jour un apprenant
     * @param string $id ID de l'apprenant
     * @param array $updated_data Nouvelles données
     * @return bool Succès de l'opération
     */
    'update_apprenant' => function($id, $updated_data) use (&$base_model) {
        $data = $base_model['read_data']();
        $index = array_search($id, array_column($data['apprenants'], 'id'));
        if ($index !== false) {
            $data['apprenants'][$index] = array_merge($data['apprenants'][$index], $updated_data);
            return $base_model['write_data']($data);
        }
        return false;
    },
    
    /**
     * Supprime un apprenant
     * @param string $id ID de l'apprenant
     * @return bool Succès de l'opération
     */
    'delete_apprenant' => function($id) use (&$base_model) {
        try {
            $data = $base_model['read_data']();
            
            // Vérification des données
            if (!isset($data['apprenants']) || !is_array($data['apprenants'])) {
                throw new \Exception("La liste des apprenants est invalide");
            }

            // Recherche de l'apprenant à supprimer
            $apprenant_index = array_search($id, array_column($data['apprenants'], 'id'));
            
            if ($apprenant_index === false) {
                throw new \Exception("Apprenant non trouvé");
            }

            // Supprimer l'apprenant du tableau principal
            unset($data['apprenants'][$apprenant_index]);
            $data['apprenants'] = array_values($data['apprenants']); // Réindexer le tableau

            // Supprimer l'apprenant de toutes les promotions
            if (isset($data['promotions']) && is_array($data['promotions'])) {
                foreach ($data['promotions'] as &$promotion) {
                    if (isset($promotion['apprenants']) && is_array($promotion['apprenants'])) {
                        $promotion['apprenants'] = array_values(array_filter(
                            $promotion['apprenants'],
                            function($a) use ($id) {
                                return isset($a['id']) && $a['id'] !== $id;
                            }
                        ));
                    }
                }
                unset($promotion); // Nettoyer la référence
            }

            // Sauvegarder les modifications
            if ($base_model['write_data']($data)) {
                return true;
            }
            
            throw new \Exception("Erreur lors de la sauvegarde des données");
            
        } catch (\Exception $e) {
            error_log("Erreur de suppression: " . $e->getMessage());
            return false;
        }
    },
    
    /**
     * Vérifie si un apprenant existe déjà (par email ou téléphone)
     * @param string|null $email Email de l'apprenant
     * @param string|null $telephone Téléphone de l'apprenant
     * @return bool True si l'apprenant existe, false sinon
     */
    'check_apprenant_exists' => function($email, $telephone) use (&$base_model) {
        $data = $base_model['read_data']();
        
        foreach ($data['apprenants'] as $apprenant) {
            if ($apprenant['email'] === $email || $apprenant['telephone'] === $telephone) {
                return true;
            }
        }
        
        return false;
    },
    
    /**
     * Ajoute un apprenant à une promotion
     * @param int $promotion_id ID de la promotion
     * @param string $apprenant_id ID de l'apprenant
     * @return bool Succès de l'opération
     */
    'add_apprenant_to_promotion' => function($promotion_id, $apprenant_id) use (&$base_model, &$apprenant_model) {
        $data = $base_model['read_data']();
        
        // Trouver l'index de la promotion
        $promotion_index = null;
        foreach ($data['promotions'] as $index => $promotion) {
            if ($promotion['id'] == $promotion_id) {
                $promotion_index = $index;
                break;
            }
        }
        
        if ($promotion_index === null) {
            return false;
        }
        
        // Initialiser le tableau des apprenants si nécessaire
        if (!isset($data['promotions'][$promotion_index]['apprenants'])) {
            $data['promotions'][$promotion_index]['apprenants'] = [];
        }
        
        // Récupérer l'apprenant
        $apprenant = $apprenant_model['get_apprenant_by_id']($apprenant_id);
        if (!$apprenant) {
            return false;
        }
        
        // Ajouter l'apprenant à la promotion
        $data['promotions'][$promotion_index]['apprenants'][] = [
            'id' => $apprenant_id,
            'name' => $apprenant['prenom'] . ' ' . $apprenant['nom']
        ];
        
        return $base_model['write_data']($data);
    },
    
    /**
     * Récupère les modules d'un apprenant
     * @param string $apprenant_id ID de l'apprenant
     * @return array Liste des modules
     */
    'get_apprenant_modules' => function($apprenant_id) use (&$base_model, &$apprenant_model) {
        $data = $base_model['read_data']();
        
        // Récupérer l'apprenant
        $apprenant = $apprenant_model['get_apprenant_by_id']($apprenant_id);
        if (!$apprenant) {
            return [];
        }

        // Récupérer la promotion de l'apprenant
        $promotion = null;
        foreach ($data['promotions'] as $p) {
            if (isset($apprenant['promotion_id']) && $p['id'] === $apprenant['promotion_id']) {
                $promotion = $p;
                break;
            }
        }

        if (!$promotion) {
            // Modules par défaut si pas de promotion
            return [
                [
                    'id' => '1',
                    'nom' => 'Algorithme & Langage C',
                    'formateur' => 'Marc Dubois',
                    'date_debut' => '2024-03-20',
                    'date_fin' => '2024-04-20',
                    'duree' => '40',
                    'status' => 'en_cours',
                    'progression' => 60
                ],
                [
                    'id' => '2',
                    'nom' => 'Frontend 1: HTML, CSS & JS',
                    'formateur' => 'Sophie Martin',
                    'date_debut' => '2024-03-14',
                    'date_fin' => '2024-04-14',
                    'duree' => '35',
                    'status' => 'en_cours',
                    'progression' => 45
                ],
                [
                    'id' => '3',
                    'nom' => 'Backend 1: PHP/MySQL avancées & POO',
                    'formateur' => 'Léa Dupont',
                    'date_debut' => '2024-03-25',
                    'date_fin' => '2024-04-25',
                    'duree' => '45',
                    'status' => 'a_venir',
                    'progression' => 0
                ]
            ];
        }

        // Si la promotion a des modules, les renvoyer
        return $promotion['modules'] ?? [];
    },
    
    /**
     * Récupère les apprenants par référentiel
     * @param string $referentiel_id ID du référentiel
     * @return array Liste des apprenants
     */
    'get_apprenants_by_referentiel' => function($referentiel_id) use (&$base_model) {
        $data = $base_model['read_data']();
        $apprenants = $data['apprenants'] ?? [];
        
        return array_filter($apprenants, function($apprenant) use ($referentiel_id) {
            return $apprenant['referentiel_id'] === $referentiel_id 
                && $apprenant['status'] === 'actif';
        });
    }
];