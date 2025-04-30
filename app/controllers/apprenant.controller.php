<?php

namespace App\Controllers;

require_once __DIR__ . '/../services/export/export.service.php';
require_once __DIR__ . '/../services/mail.service.php';
require_once __DIR__ . '/../middleware/auth.middleware.php';  
use App\Services\Export;
use App\Middleware;  // Add this line

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/../models/model.php';
require_once __DIR__ . '/../services/validator.service.php';
require_once __DIR__ . '/../services/session.service.php';
require_once __DIR__ . '/../services/file.service.php';

use Exception;

function list_apprenants() {
    global $model, $session_services;
    
    try {
        check_auth();
        
        // Paramètres de pagination et filtres
        $current_page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
        $items_per_page = isset($_GET['items_per_page']) ? (int)$_GET['items_per_page'] : 10;
        
        // Récupérer les filtres depuis l'URL
        $search = $_GET['search'] ?? '';
        $referentiel_filter = $_GET['referentiel'] ?? '';
        $status_filter = $_GET['status'] ?? '';

        // Récupérer les données
        $apprenants = $model['get_all_apprenants']();
        $referentiels = $model['get_all_referentiels']();
        
        // Récupérer le référentiel sélectionné
        $selected_referentiel = null;
        if (!empty($referentiel_filter)) {
            foreach ($referentiels as $ref) {
                if ($ref['id'] === $referentiel_filter) {
                    $selected_referentiel = $ref;
                    break;
                }
            }
        }

        // Filtrage des apprenants
        $filtered_apprenants = array_filter($apprenants, function($apprenant) use ($search, $referentiel_filter, $status_filter) {
            $matches = true;
            
            // Filtre par référentiel
            if (!empty($referentiel_filter)) {
                $matches = $matches && (isset($apprenant['referentiel_id']) && $apprenant['referentiel_id'] === $referentiel_filter);
            }
            
            // Filtre par statut
            if (!empty($status_filter)) {
                $matches = $matches && (isset($apprenant['status']) && $apprenant['status'] === $status_filter);
            }
            
            // Filtre par recherche
            if (!empty($search)) {
                $search_lower = strtolower($search);
                $full_name = strtolower($apprenant['prenom'] . ' ' . $apprenant['nom']);
                $matricule = strtolower($apprenant['matricule']);
                $matches = $matches && (
                    strpos($full_name, $search_lower) !== false || 
                    strpos($matricule, $search_lower) !== false
                );
            }
            
            return $matches;
        });

        // Réindexer le tableau après filtrage
        $filtered_apprenants = array_values($filtered_apprenants);
        
        // Calcul de la pagination
        $total_items = count($filtered_apprenants);
        $total_pages = max(1, ceil($total_items / $items_per_page));
        $current_page = max(1, min($current_page, $total_pages));
        $start = ($current_page - 1) * $items_per_page;
        $end = min($start + $items_per_page, $total_items);
        
        // Extraire les éléments de la page courante
        $paginated_apprenants = array_slice($filtered_apprenants, $start, $items_per_page);

        // Render avec toutes les données nécessaires
        render('admin.layout.view.php', 'apprenants/student-list.view.php', [
            'apprenants' => $paginated_apprenants,
            'referentiels' => $referentiels,
            'selected_referentiel' => $selected_referentiel,
            'filters' => [
                'search' => $search,
                'referentiel' => $referentiel_filter,
                'status' => $status_filter
            ],
            'pagination' => [
                'current_page' => $current_page,
                'total_pages' => $total_pages,
                'total_items' => $total_items,
                'start' => $start + 1,
                'end' => $end,
                'items_per_page' => $items_per_page
            ]
        ]);
        
    } catch (Exception $e) {
        $session_services['set_flash_message']('error', 'Une erreur est survenue');
        redirect('?page=dashboard');
    }
}

function add_apprenant_form() {
    global $model, $session_services;
    
    try {
        check_auth();
        
        // Vérifier si une promotion est active
        $current_promotion = $model['get_current_promotion']();
        
        // Vérifier si la promotion est active ET en cours
        if (!$current_promotion || $current_promotion['etat'] !== 'en_cours') {
            $session_services['set_flash_message']('error', 
                'Impossible d\'ajouter un apprenant. Aucune promotion active en cours.'
            );
            redirect('?page=apprenants');
            return;
        }
        
        // Récupérer la liste des référentiels de la promotion active
        $referentiels = array_filter(
            $model['get_all_referentiels'](),
            function($ref) use ($current_promotion) {
                return in_array($ref['id'], $current_promotion['referentiels'] ?? []);
            }
        );
        
        // Afficher le formulaire
        render('admin.layout.view.php', 'apprenants/student-add.view.php', [
            'referentiels' => $referentiels,
            'current_promotion' => $current_promotion
        ]);
        
    } catch (Exception $e) {
        $session_services['set_flash_message']('error', 'Une erreur est survenue');
        redirect('?page=apprenants');
    }
}

function add_apprenant_process() {
    global $model, $session_services, $file_services, $mail_services;
    
    try {
        check_auth();
        
        // Vérifier si une promotion est active et en cours
        $current_promotion = $model['get_current_promotion']();
        if (!$current_promotion || $current_promotion['etat'] !== 'en_cours') {
            $session_services['set_flash_message']('error', 
                'Impossible d\'ajouter un apprenant. Aucune promotion active en cours.'
            );
            redirect('?page=apprenants');
            return;
        }

        // Validation des données
        $email = htmlspecialchars($_POST['email']);
        $telephone = htmlspecialchars($_POST['telephone']);
        
        // Vérification des doublons
        if ($model['check_apprenant_exists']($email, $telephone)) {
            $session_services['set_flash_message']('error', 'Un apprenant avec cet email ou ce numéro de téléphone existe déjà');
            redirect('?page=add-apprenant');
            return;
        }
        
        // Traitement de l'image
        $image_path = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $image_path = $file_services['handle_upload']($_FILES['photo'], 'apprenants');
            if (!$image_path) {
                $session_services['set_flash_message']('error', 'Erreur lors du téléchargement de l\'image');
                redirect('?page=add-apprenant');
                return;
            }
        }

        // Préparation des données avec la promotion active
        $apprenant_data = [
            'id' => uniqid(),
            'matricule' => $model['generate_matricule'](),
            'prenom' => htmlspecialchars($_POST['prenom']),
            'nom' => htmlspecialchars($_POST['nom']),
            'email' => htmlspecialchars($_POST['email']),
            'telephone' => htmlspecialchars($_POST['telephone']),
            'adresse' => htmlspecialchars($_POST['adresse']),
            'referentiel_id' => $_POST['referentiel_id'],
            'photo' => $image_path,
            'status' => 'actif',
            'created_at' => date('Y-m-d H:i:s'),
            'promotion_id' => $current_promotion['id'] // Ajout de l'ID de la promotion active
        ];

        // Ajout de l'apprenant
        if ($model['add_apprenant']($apprenant_data)) {
            // Mise à jour de la promotion
            $model['add_apprenant_to_promotion']($current_promotion['id'], $apprenant_data['id']);
            
            // Envoi de l'email de confirmation
            if ($mail_services['send_welcome_email']($apprenant_data)) {
                $session_services['set_flash_message']('success', 
                    'Apprenant ajouté avec succès. Un email de confirmation a été envoyé.'
                );
            } else {
                $session_services['set_flash_message']('warning', 
                    'Apprenant ajouté avec succès mais l\'email n\'a pas pu être envoyé.'
                );
            }
            
            redirect('?page=apprenants');
        } else {
            $session_services['set_flash_message']('error', 'Erreur lors de l\'ajout de l\'apprenant');
            redirect('?page=add-apprenant');
        }

    } catch (Exception $e) {
        $session_services['set_flash_message']('error', 'Une erreur est survenue');
        redirect('?page=add-apprenant');
    }
}

// Fonction helper pour la validation
function validate_apprenant_data($post_data, $files) {
    $errors = [];
    
    // Validation des champs requis
    $required_fields = ['prenom', 'nom', 'email', 'telephone', 'referentiel_id'];
    foreach ($required_fields as $field) {
        if (empty($post_data[$field])) {
            $errors[] = "Le champ $field est requis";
        }
    }
    
    // Validation de l'email
    if (!filter_var($post_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide";
    }
    
    // Validation du téléphone (format simple)
    if (!preg_match("/^(77|78|75|70|76)[0-9]{7}$/", $post_data['telephone'])) {
        $errors[] = "Le numéro de téléphone doit être au format sénégalais (7X XXX XX XX)";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

function download_list() {
    global $model, $session_services;
    
    try {
        check_auth();
        
        $format = $_GET['format'] ?? 'pdf';
        $apprenants = $model['get_all_apprenants']();
        
        if ($format === 'pdf') {
            Export\generate_pdf($apprenants);
        } else if ($format === 'excel') {
            Export\generate_excel($apprenants);
        }
        
    } catch (Exception $e) {
        $session_services['set_flash_message']('error', 'Erreur lors du téléchargement');
        redirect('?page=apprenants');
    }
}

function show_apprenant_details() {
    global $model, $session_services;
    
    try {
        check_auth();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('?page=apprenants');
        }
        
        $apprenant = $model['get_apprenant_by_id']($id);
        if (!$apprenant) {
            $session_services['set_flash_message']('error', 'Apprenant non trouvé');
            redirect('?page=apprenants');
        }
        
        // Récupérer le référentiel
        $referentiel = isset($apprenant['referentiel_id']) ? 
            $model['get_referentiel_by_id']($apprenant['referentiel_id']) : 
            null;
        $apprenant['referentiel_name'] = $referentiel ? $referentiel['name'] : 'Non défini';
        
        // Récupérer la promotion active
        $promotion_active = $model['get_promotion_by_id']($apprenant['promotion_id'] ?? null);
        
        // Récupérer les modules
        $modules = $model['get_apprenant_modules']($id);
        
        // Classer les modules par statut
        $modules_en_cours = array_filter($modules, fn($m) => $m['status'] === 'en_cours');
        $modules_a_venir = array_filter($modules, fn($m) => $m['status'] === 'a_venir');
        $modules_termines = array_filter($modules, fn($m) => $m['status'] === 'termine');
        
        render('admin.layout.view.php', 'apprenants/student-details.view.php', [
            'apprenant' => $apprenant,
            'promotion_active' => $promotion_active,
            'modules' => $modules,
            'modules_en_cours' => $modules_en_cours,
            'modules_a_venir' => $modules_a_venir,
            'modules_termines' => $modules_termines
        ]);
        
    } catch (Exception $e) {
        $session_services['set_flash_message']('error', 'Une erreur est survenue');
        redirect('?page=apprenants');
    }
}

function show_apprenant_profile() {
    global $model;
    
    $user = Middleware\check_apprenant_auth();
    
    $apprenant = $model['get_apprenant_by_id']($user['id']);
    if (!$apprenant) {
        redirect('?page=login');
    }
    
    // Récupérer les informations supplémentaires
    $referentiel = null;
    if (isset($apprenant['referentiel_id'])) {
        $referentiel = $model['get_referentiel_by_id']($apprenant['referentiel_id']);
    }
    
    $promotion = null;
    if (isset($apprenant['promotion_id'])) {
        $promotion = $model['get_promotion_by_id']($apprenant['promotion_id']);
    }
    
    // Utiliser le layout apprenant au lieu du layout admin
    render('apprenant.layout.view.php', 'apprenants/profile.view.php', [
        'apprenant' => $apprenant,
        'referentiel' => $referentiel,
        'promotion' => $promotion
    ]);
}

function exclude_apprenant() {
    global $model, $session_services;
    
    try {
        check_auth();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $session_services['set_flash_message']('error', 'ID de l\'apprenant non spécifié');
            redirect('?page=apprenants');
            return;
        }
        
        // Mettre à jour le statut de l'apprenant
        if ($model['update_apprenant']($id, ['status' => 'exclu'])) {
            $session_services['set_flash_message']('success', 'Apprenant exclu avec succès');
        } else {
            $session_services['set_flash_message']('error', 'Erreur lors de l\'exclusion');
        }
        
        redirect('?page=apprenant-details&id=' . $id);
        
    } catch (Exception $e) {
        $session_services['set_flash_message']('error', 'Une erreur est survenue');
        redirect('?page=apprenants');
    }
}

function edit_apprenant_form() {
    global $model, $session_services;
    
    try {
        check_auth();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $session_services['set_flash_message']('error', 'ID de l\'apprenant non spécifié');
            redirect('?page=apprenants');
            return;
        }
        
        $apprenant = $model['get_apprenant_by_id']($id);
        if (!$apprenant) {
            $session_services['set_flash_message']('error', 'Apprenant non trouvé');
            redirect('?page=apprenants');
            return;
        }
        
        // Récupérer tous les référentiels
        $referentiels = $model['get_all_referentiels']();
        
        render('admin.layout.view.php', 'apprenants/student-edit.view.php', [
            'apprenant' => $apprenant,
            'referentiels' => $referentiels
        ]);
        
    } catch (Exception $e) {
        $session_services['set_flash_message']('error', 'Une erreur est survenue');
        redirect('?page=apprenants');
    }
}

function edit_apprenant_process() {
    global $model, $session_services, $file_services;
    
    try {
        check_auth();
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $session_services['set_flash_message']('error', 'ID de l\'apprenant non spécifié');
            redirect('?page=apprenants');
            return;
        }
        
        // Récupérer l'apprenant existant
        $apprenant = $model['get_apprenant_by_id']($id);
        if (!$apprenant) {
            $session_services['set_flash_message']('error', 'Apprenant non trouvé');
            redirect('?page=apprenants');
            return;
        }
        
        // Traiter la nouvelle photo si elle existe
        $image_path = $apprenant['photo'];
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $new_image = $file_services['handle_upload']($_FILES['photo'], 'apprenants');
            if ($new_image) {
                // Supprimer l'ancienne photo si elle existe
                if ($image_path && file_exists(__DIR__ . '/../../public/' . $image_path)) {
                    unlink(__DIR__ . '/../../public/' . $image_path);
                }
                $image_path = $new_image;
            }
        }
        
        // Préparer les données à mettre à jour
        $updated_data = [
            'prenom' => htmlspecialchars($_POST['prenom']),
            'nom' => htmlspecialchars($_POST['nom']),
            'email' => htmlspecialchars($_POST['email']),
            'telephone' => htmlspecialchars($_POST['telephone']),
            'adresse' => htmlspecialchars($_POST['adresse']),
            'referentiel_id' => $_POST['referentiel_id'],
            'photo' => $image_path
        ];
        
        // Mettre à jour l'apprenant
        if ($model['update_apprenant']($id, $updated_data)) {
            $session_services['set_flash_message']('success', 'Informations mises à jour avec succès');
            redirect('?page=apprenant-details&id=' . $id);
        } else {
            $session_services['set_flash_message']('error', 'Erreur lors de la mise à jour');
            redirect('?page=edit-apprenant&id=' . $id);
        }
        
    } catch (Exception $e) {
        $session_services['set_flash_message']('error', 'Une erreur est survenue');
        redirect('?page=apprenants');
    }
}

function import_apprenants_form() {
    global $model, $session_services;
    
    try {
        check_auth();
        render('admin.layout.view.php', 'apprenants/import-apprenants.view.php');
    } catch (Exception $e) {
        $session_services['set_flash_message']('error', 'Une erreur est survenue');
        redirect('?page=apprenants');
    }
}

function import_apprenants_process() {
    global $model, $session_services;
    
    try {
        check_auth();
        
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $session_services['set_flash_message']('error', 'Veuillez sélectionner un fichier Excel valide');
            redirect('?page=import-apprenants');
            return;
        }

        // Use the correct path to autoload.php
        require __DIR__ . '/../../vendor/autoload.php';
        
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES['excel_file']['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // Enlever l'en-tête
        array_shift($rows);
        
        $import_errors = [];
        $success_count = 0;
        
        foreach ($rows as $index => $row) {
            $email = $row[2];
            $telephone = $row[3];
            
            // Vérifier si l'apprenant existe déjà
            if ($model['check_apprenant_exists']($email, $telephone)) {
                $import_errors[] = "Ligne " . ($index + 2) . ": Un apprenant avec cet email ($email) ou ce téléphone ($telephone) existe déjà";
                continue;
            }
            
            // Get current promotion
            $current_promotion = $model['get_current_promotion']();
            if (!$current_promotion) {
                $import_errors[] = "Ligne " . ($index + 2) . ": Aucune promotion active";
                continue;
            }
            
            // Créer l'apprenant with default referentiel_id
            $apprenant_data = [
                'id' => uniqid(),
                'matricule' => $model['generate_matricule'](),
                'prenom' => $row[0],
                'nom' => $row[1],
                'email' => $email,
                'telephone' => $telephone,
                'adresse' => $row[4],
                'referentiel_id' => null, // Set default referentiel_id
                'status' => 'actif',
                'created_at' => date('Y-m-d H:i:s'),
                'promotion_id' => $current_promotion['id']
            ];
            
            if ($model['add_apprenant']($apprenant_data)) {
                $success_count++;
            } else {
                $import_errors[] = "Ligne " . ($index + 2) . ": Erreur lors de l'ajout de l'apprenant";
            }
        }
        
        if (empty($import_errors)) {
            $session_services['set_flash_message']('success', "$success_count apprenants importés avec succès");
            redirect('?page=apprenants');
        } else {
            render('admin.layout.view.php', 'apprenants/import-apprenants.view.php', [
                'import_errors' => $import_errors
            ]);
        }
        
    } catch (Exception $e) {
        $session_services['set_flash_message']('error', 'Une erreur est survenue lors de l\'importation');
        redirect('?page=import-apprenants');
    }
}

function delete_apprenant_process() {
    global $model, $session_services;
    
    try {
        check_auth();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            throw new \Exception('ID de l\'apprenant non spécifié');
        }
        
        // Récupérer l'apprenant pour obtenir le chemin de la photo
        $apprenant = $model['get_apprenant_by_id']($id);
        if (!$apprenant) {
            throw new \Exception('Apprenant non trouvé');
        }
        
        // Supprimer la photo si elle existe
        if (!empty($apprenant['photo'])) {
            $photo_path = __DIR__ . '/../../public/' . $apprenant['photo'];
            if (file_exists($photo_path)) {
                unlink($photo_path);
            }
        }
        
        // Supprimer l'apprenant
        if ($model['delete_apprenant']($id)) {
            $session_services['set_flash_message']('success', 'Apprenant supprimé avec succès');
        } else {
            throw new \Exception('Erreur lors de la suppression');
        }
        
        redirect('?page=apprenants');
        
    } catch (\Exception $e) {
        $session_services['set_flash_message']('error', $e->getMessage());
        redirect('?page=apprenants');
    }
}