<?php

namespace App\Models;

require_once __DIR__ . '/../enums/path.enum.php';
require_once __DIR__ . '/../enums/status.enum.php';
require_once __DIR__ . '/../enums/profile.enum.php';

use App\Enums;
use \Exception; // Modification ici pour utiliser l'Exception globale

// Collection de toutes les fonctions modèles pour l'application
$model = [
    // Fonctions de base pour manipuler les données
    'read_data' => function () {
        if (!file_exists(Enums\DATA_PATH)) {
            // Si le fichier n'existe pas, on renvoie une structure par défaut
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
            // En cas d'erreur de décodage JSON
            return [
                'users' => [],
                'promotions' => [],
                'referentiels' => [],
                'apprenants' => []
            ];
        }
        
        return $data;
    },
    
    'write_data' => function ($data) {
        // Vérifier si le dossier data existe, sinon le créer
        $data_dir = dirname(Enums\DATA_PATH);
        if (!is_dir($data_dir)) {
            if (!mkdir($data_dir, 0777, true)) {
                throw new Exception("Impossible de créer le dossier data");
            }
        }
        
        // Vérifier les permissions
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
    
    'generate_id' => function () {
        return uniqid();
    },
    
    // Fonctions d'authentification
    'authenticate' => function ($email, $password) use (&$model) {
        $data = $model['read_data']();
        
        // Utiliser array_filter au lieu de foreach
        $filtered_users = array_filter($data['users'], function ($user) use ($email, $password) {
            return $user['email'] === $email && $user['password'] === $password;
        });
        
        // Si aucun utilisateur ne correspond
        if (empty($filtered_users)) {
            return null;
        }
        
        // Récupérer le premier utilisateur qui correspond
        return reset($filtered_users);
    },
    
    'get_user_by_email' => function ($email) use (&$model) {
        $data = $model['read_data']();
        
        // Utiliser array_filter au lieu de foreach
        $filtered_users = array_filter($data['users'], function ($user) use ($email) {
            return $user['email'] === $email;
        });
        
        // Si aucun utilisateur ne correspond
        if (empty($filtered_users)) {
            return null;
        }
        
        // Récupérer le premier utilisateur qui correspond
        return reset($filtered_users);
    },
    
    'get_user_by_id' => function ($user_id) use (&$model) {
        $data = $model['read_data']();
        
        // Utiliser array_filter au lieu de foreach
        $filtered_users = array_filter($data['users'], function ($user) use ($user_id) {
            return $user['id'] === $user_id;
        });
        
        // Si aucun utilisateur ne correspond
        if (empty($filtered_users)) {
            return null;
        }
        
        // Récupérer le premier utilisateur qui correspond
        return reset($filtered_users);
    },
    
    'change_password' => function ($user_id, $new_password) use (&$model) {
        $data = $model['read_data']();
        
        $user_indices = array_keys(array_filter($data['users'], function($user) use ($user_id) {
            return $user['id'] === $user_id;
        }));
        
        if (empty($user_indices)) {
            return false;
        }
        
        $user_index = reset($user_indices);
        
        // Mettre à jour le mot de passe (sans cryptage)
        $data['users'][$user_index]['password'] = $new_password;
        
        // Sauvegarder les modifications
        return $model['write_data']($data);
    },
    
    // Fonctions pour les promotions
    'get_all_promotions' => function () use (&$model) {
        $data = $model['read_data']();
        return $data['promotions'] ?? [];
    },
    
    'get_promotion_by_id' => function ($id) use (&$model) {
        $data = $model['read_data']();
        
        // Utiliser array_filter au lieu de foreach
        $filtered_promotions = array_filter($data['promotions'] ?? [], function ($promotion) use ($id) {
            return $promotion['id'] === $id;
        });
        
        return !empty($filtered_promotions) ? reset($filtered_promotions) : null;
    },
    
    'promotion_name_exists' => function(string $name) use (&$model): bool {
        $data = $model['read_data']();
        
        foreach ($data['promotions'] as $promotion) {
            if (strtolower($promotion['name']) === strtolower($name)) {
                return true;
            }
        }
        
        return false;
    },
    
    'create_promotion' => function(array $promotion_data) use (&$model) {
        $data = $model['read_data']();
        
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
        return $model['write_data']($data);
    },
    
    'update_promotion' => function ($id, $promotion_data) use (&$model) {
        $data = $model['read_data']();
        
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
        
        if ($model['write_data']($data)) {
            return $data['promotions'][$promotion_index];
        }
        
        return null;
    },
    
    'toggle_promotion_status' => function(int $promotion_id) use (&$model) {
        $data = $model['read_data']();
        
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
        if ($model['write_data']($data)) {
            return $data['promotions'][$target_index];
        }
        
        return null;
    },
    
    'update_promotion_status' => function(int $promotion_id, string $status) use (&$model) {
        $data = $model['read_data']();
        
        foreach ($data['promotions'] as &$promotion) {
            if ((int)$promotion['id'] === $promotion_id) {
                $promotion['status'] = $status;
                return $model['write_data']($data);
            }
        }
        
        return false;
    },
    
    'search_promotions' => function($search_term) use (&$model) {
        $promotions = $model['get_all_promotions']();
        
        if (empty($search_term)) {
            return $promotions;
        }
        
        return array_values(array_filter($promotions, function($promotion) use ($search_term) {
            return stripos($promotion['name'], $search_term) !== false;
        }));
    },
    
    // Fonctions pour les référentiels
    'get_all_referentiels' => function () use (&$model) {
        $data = $model['read_data']();
        return $data['referentiels'] ?? [];
    },
    
    'get_referentiel_by_id' => function ($id) use (&$model) {
        $data = $model['read_data']();
        
        // Utiliser array_filter au lieu de foreach
        $filtered_referentiels = array_filter($data['referentiels'] ?? [], function ($referentiel) use ($id) {
            return $referentiel['id'] === $id;
        });
        
        return !empty($filtered_referentiels) ? reset($filtered_referentiels) : null;
    },
    
    'referentiel_name_exists' => function ($name, $exclude_id = null) use (&$model) {
        $data = $model['read_data']();
        
        // Utiliser array_filter au lieu de foreach
        $filtered_referentiels = array_filter($data['referentiels'] ?? [], function ($referentiel) use ($name, $exclude_id) {
            return strtolower($referentiel['name']) === strtolower($name) && ($exclude_id === null || $referentiel['id'] !== $exclude_id);
        });
        
        return !empty($filtered_referentiels);
    },
    
    'create_referentiel' => function ($referentiel_data) use (&$model) {
        $data = $model['read_data']();
        
        // Générer un ID unique
        $referentiel_data['id'] = uniqid();
        
        // Ajouter le référentiel à la liste
        $data['referentiels'][] = $referentiel_data;
        
        // Sauvegarder les modifications
        return $model['write_data']($data);
    },
    
    'get_referentiels_by_promotion' => function($promotion_id) use (&$model) {
        $data = $model['read_data']();
        
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
    
    'assign_referentiels_to_promotion' => function ($promotion_id, $referentiel_ids) use (&$model) {
        $data = $model['read_data']();
        
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
        
        return $model['write_data']($data);
    },
    
    'search_referentiels' => function(string $query) use (&$model) {
        $referentiels = $model['get_all_referentiels']();
        if (empty($query)) {
            return $referentiels;
        }
        
        return array_filter($referentiels, function($ref) use ($query) {
            return stripos($ref['name'], $query) !== false || 
                   stripos($ref['description'], $query) !== false;
        });
    },
    
    'get_referentiel_by_name' => function($name) use (&$model) {
        $data = $model['read_data']();
        foreach ($data['referentiels'] as $ref) {
            if (strtolower($ref['name']) === strtolower($name)) {
                return $ref;
            }
        }
        return null;
    },
    
    // Fonction pour récupérer la promotion active courante
    'get_current_promotion' => function () use (&$model) {
        $data = $model['read_data']();
        
        // Utiliser array_filter au lieu de foreach
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
    
    // Statistiques diverses pour le tableau de bord
    'get_promotions_stats' => function () use (&$model) {
        $data = $model['read_data']();
        
        // Nombre total de promotions
        $total_promotions = count($data['promotions'] ?? []);
        
        // Nombre de promotions actives
        $active_promotions = count(array_filter($data['promotions'] ?? [], function ($promotion) {
            return $promotion['status'] === Enums\ACTIVE;
        }));
        
        // Récupérer la promotion courante
        $current_promotion = $model['get_current_promotion']();
        
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
    },
    
    // Fonctions pour les apprenants
    'get_all_apprenants' => function () use (&$model) {
        $data = $model['read_data']();
        return $data['apprenants'] ?? [];
    },
    
    'get_apprenants_by_promotion' => function ($promotion_id) use (&$model) {
        $data = $model['read_data']();
        
        // Filtrer les apprenants par promotion
        $apprenants = array_filter($data['apprenants'] ?? [], function ($apprenant) use ($promotion_id) {
            return $apprenant['promotion_id'] === $promotion_id;
        });
        
        return array_values($apprenants);
    },
    
    'get_apprenant_by_id' => function ($id) use (&$model) {
        $data = $model['read_data']();
        
        // Filtrer les apprenants par ID
        $filtered_apprenants = array_filter($data['apprenants'] ?? [], function ($apprenant) use ($id) {
            return $apprenant['id'] === $id;
        });
        
        return !empty($filtered_apprenants) ? reset($filtered_apprenants) : null;
    },
    
    'get_apprenant_by_matricule' => function ($matricule) use (&$model) {
        $data = $model['read_data']();
        
        // Filtrer les apprenants par matricule
        $filtered_apprenants = array_filter($data['apprenants'] ?? [], function ($apprenant) use ($matricule) {
            return $apprenant['matricule'] === $matricule;
        });
        
        return !empty($filtered_apprenants) ? reset($filtered_apprenants) : null;
    },
    
    'generate_matricule' => function () use (&$model) {
        $data = $model['read_data']();
        $year = date('Y');
        $count = count($data['apprenants'] ?? []) + 1;
        
        return 'ODC-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    },
    
    'get_statistics' => function() use (&$model) {
        $data = $model['read_data']();
        
        // Trouver la promotion active
        $active_promotions = array_filter($data['promotions'], function($promotion) {
            return $promotion['status'] === 'active';
        });
        $active_promotion = reset($active_promotions);
        
        // Calculer les statistiques
        $stats = [
            'active_learners' => 0,
            'total_referentials' => count($data['referentiels'] ?? []),
            'active_promotions' => count($active_promotions),
            'total_promotions' => count($data['promotions'] ?? [])
        ];
        
        // Ajouter le nombre d'apprenants de la promotion active
        if ($active_promotion) {
            $stats['active_learners'] = count($active_promotion['apprenants'] ?? []);
        }
        
        return $stats;
    },
    'referentiel_has_apprenants' => function ($promotion_id, $referentiel_id) use (&$model) {
    $data = $model['read_data']();
    
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

'unassign_referentiel_from_promotion' => function ($promotion_id, $referentiel_id) use (&$model) {
    $data = $model['read_data']();
    
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
    
    return $model['write_data']($data);
},
'update_promotion_termination' => function($promotion_id, $is_terminated) use (&$model) {
    $data = $model['read_data']();
    
    foreach ($data['promotions'] as &$promotion) {
        if ($promotion['id'] == $promotion_id) {
            $promotion['status'] = $is_terminated ? 'terminee' : 'active';
            return $model['write_data']($data);
        }
    }
    
    return false;
},
'add_apprenant' => function($apprenant_data) use (&$model) {
    try {
        $data = $model['read_data']();
        
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
        if ($model['write_data']($data)) {
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
'update_apprenant' => function($id, $updated_data) use (&$model) {
    $data = $model['read_data']();
    $index = array_search($id, array_column($data['apprenants'], 'id'));
    if ($index !== false) {
        $data['apprenants'][$index] = array_merge($data['apprenants'][$index], $updated_data);
        return $model['write_data']($data);
    }
    return false;
},
'delete_apprenant' => function($id) use (&$model) {
    try {
        $data = $model['read_data']();
        
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
        if ($model['write_data']($data)) {
            return true;
        }
        
        throw new \Exception("Erreur lors de la sauvegarde des données");
        
    } catch (\Exception $e) {
        error_log("Erreur de suppression: " . $e->getMessage());
        return false;
    }
},
'check_apprenant_exists' => function($email, $telephone) use (&$model) {
        $data = $model['read_data']();
        
        foreach ($data['apprenants'] as $apprenant) {
            if ($apprenant['email'] === $email || $apprenant['telephone'] === $telephone) {
                return true;
            }
        }
        
        return false;
    },
    'add_apprenant_to_promotion' => function($promotion_id, $apprenant_id) use (&$model) {
    $data = $model['read_data']();
    
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
    
    // Ajouter l'apprenant à la promotion
    $data['promotions'][$promotion_index]['apprenants'][] = [
        'id' => $apprenant_id,
        'name' => $model['get_apprenant_by_id']($apprenant_id)['prenom'] . ' ' . 
                 $model['get_apprenant_by_id']($apprenant_id)['nom']
    ];
    
    return $model['write_data']($data);
},
'get_apprenant_modules' => function($apprenant_id) use (&$model) {
    $data = $model['read_data']();
    
    // Récupérer l'apprenant
    $apprenant = $model['get_apprenant_by_id']($apprenant_id);
    if (!$apprenant) {
        return [];
    }

    // Récupérer la promotion de l'apprenant
    $promotion = $model['get_promotion_by_id']($apprenant['promotion_id'] ?? null);
    if (!$promotion) {
        return [];
    }

    // Liste des modules par défaut de la formation
    $modules = [
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
        ],
        [
            'id' => '4',
            'nom' => 'Frontend 2: JS & TS + Tailwind',
            'formateur' => 'Thomas Legrand',
            'date_debut' => '2024-05-01',
            'date_fin' => '2024-05-30',
            'duree' => '40',
            'status' => 'a_venir',
            'progression' => 0
        ],
        [
            'id' => '5',
            'nom' => 'Backend 2: Laravel & SOLID',
            'formateur' => 'Antoine Moreau',
            'date_debut' => '2024-06-01',
            'date_fin' => '2024-06-30',
            'duree' => '50',
            'status' => 'a_venir',
            'progression' => 0
        ],
        [
            'id' => '6',
            'nom' => 'Frontend 3: React.js',
            'formateur' => 'Clara Blanc',
            'date_debut' => '2024-07-01',
            'date_fin' => '2024-07-31',
            'duree' => '45',
            'status' => 'a_venir',
            'progression' => 0
        ]
    ];

    // Si la promotion existe et a des modules personnalisés, les utiliser à la place
    if ($promotion && isset($promotion['modules']) && !empty($promotion['modules'])) {
        return $promotion['modules'];
    }

    return $modules;
},
// Ajouter cette fonction dans le tableau $model
'authenticate_apprenant' => function($login, $password) use (&$model) {
    try {
        $data = $model['read_data']();
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
'get_apprenants_by_referentiel' => function($referentiel_id) use (&$model) {
    $data = $model['read_data']();
    $apprenants = $data['apprenants'] ?? [];
    
    return array_filter($apprenants, function($apprenant) use ($referentiel_id) {
        return $apprenant['referentiel_id'] === $referentiel_id 
            && $apprenant['status'] === 'actif';
    });
},
];

?>