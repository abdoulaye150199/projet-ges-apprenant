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
        $search = $_GET['search'] ?? '';
        $referentiel_filter = $_GET['referentiel'] ?? '';
        $status_filter = $_GET['status'] ?? '';
        $tab = $_GET['tab'] ?? 'retained'; // 'retained' ou 'waiting'

        // Récupérer les données
        $apprenants = $model['get_all_apprenants']();
        $referentiels = $model['get_all_referentiels']();

        // Vérifier si les informations sont complètes
        function hasIncompleteInfo($apprenant) {
            $required_fields = ['prenom', 'nom', 'email', 'telephone', 'adresse', 'referentiel_id'];
            foreach ($required_fields as $field) {
                if (empty($apprenant[$field])) {
                    return true;
                }
            }
            return false;
        }

        // Filtrer les apprenants selon le tab
        $apprenants = array_filter($apprenants, function($apprenant) use ($tab) {
            $isComplete = !hasIncompleteInfo($apprenant);
            return $tab === 'retained' ? $isComplete : !$isComplete;
        });

        // Appliquer les autres filtres
        $filtered_apprenants = array_filter($apprenants, function($apprenant) use ($search, $referentiel_filter, $status_filter) {
            $matches = true;
            
            if (!empty($referentiel_filter)) {
                $matches = $matches && ($apprenant['referentiel_id'] === $referentiel_filter);
            }
            
            if (!empty($status_filter)) {
                $matches = $matches && ($apprenant['status'] === $status_filter);
            }
            
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

        // Pagination
        $total_items = count($filtered_apprenants);
        $total_pages = max(1, ceil($total_items / $items_per_page));
        $current_page = max(1, min($current_page, $total_pages));
        $start = ($current_page - 1) * $items_per_page;
        $end = min($start + $items_per_page, $total_items);
        
        $paginated_apprenants = array_slice($filtered_apprenants, $start, $items_per_page);

        render('admin.layout.view.php', 'apprenants/student-list.view.php', [
            'apprenants' => $paginated_apprenants,
            'referentiels' => $referentiels,
            'current_tab' => $tab,
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
    global $model, $validator_services, $session_services;
    
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('?page=add-apprenant');
            return;
        }

        $apprenant_data = [
            'prenom' => $_POST['prenom'] ?? '',
            'nom' => $_POST['nom'] ?? '',
            'email' => $_POST['email'] ?? '',
            'telephone' => $_POST['telephone'] ?? '',
            'adresse' => $_POST['adresse'] ?? '',
            'date_naissance' => $_POST['date_naissance'] ?? '',
            'lieu_naissance' => $_POST['lieu_naissance'] ?? '',
            'referentiel_id' => $_POST['referentiel_id'] ?? '' // Changé de 'referentiel' à 'referentiel_id'
        ];

        // Validation avec les fichiers optionnels
        $validation_result = validate_apprenant_data($apprenant_data, $_FILES);
        
        if (!$validation_result['valid']) {
            $session_services['set_flash_message']('error', implode('<br>', $validation_result['errors']));
            redirect('?page=add-apprenant');
            return;
        }

        // Ajout de l'apprenant
        $result = $model['add_apprenant']($apprenant_data);

        if ($result['success']) {
            $message = 'Apprenant ajouté avec succès';
            if ($result['mail_sent']) {
                $message .= ' et les identifiants ont été envoyés par email';
            } else {
                $message .= ' mais l\'envoi du mail a échoué';
            }
            $session_services['set_flash_message']('success', $message);
            redirect('?page=apprenants');
        } else {
            $session_services['set_flash_message']('error', $result['message']);
            redirect('?page=add-apprenant');
        }

    } catch (Exception $e) {
        $session_services['set_flash_message']('error', 'Une erreur est survenue');
        redirect('?page=add-apprenant');
    }
}

// Fonction helper pour la validation
function validate_apprenant_data($post_data, $files = null) {
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

function validate_import_data($row) {
    $errors = [];
    $missing_fields = [];
    $required_fields = [
        'prenom', 'nom', 'email', 'telephone', 'referentiel_id',
        'adresse', 'date_naissance', 'lieu_naissance'
    ];

    // Vérifier les champs requis
    foreach ($required_fields as $index => $field) {
        if (empty($row[$index])) {
            $missing_fields[] = $field;
        }
    }

    // Validation des champs présents
    if (!empty($row[2]) && !filter_var($row[2], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }

    if (!empty($row[3]) && !preg_match("/^(77|78|75|70|76)[0-9]{7}$/", $row[3])) {
        $errors[] = "Le numéro de téléphone n'est pas au format valide";
    }

    return [
        'valid' => empty($errors) && empty($missing_fields),
        'errors' => $errors,
        'missing_fields' => $missing_fields,
        'incomplete' => !empty($missing_fields)
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
        render('admin.layout.view.php', 'apprenants/import-apprenants.view.php', [
            'import_ui' => '
                <div class="import-container">
                    <div class="card">
                        <div class="card-header">
                            <h2>Importer des apprenants</h2>
                        </div>
                        <div class="card-body">
                            <div class="template-download mb-4">
                                <p>Téléchargez le modèle de fichier CSV et remplissez-le avec vos données :</p>
                                <a href="?page=download-template" class="btn btn-secondary">
                                    <i class="fas fa-download"></i> Télécharger le modèle
                                </a>
                            </div>

                            <form action="?page=import-apprenants-process" method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="file">Fichier CSV</label>
                                    <input type="file" 
                                           name="file" 
                                           id="file" 
                                           class="form-control" 
                                           accept=".csv" 
                                           required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Importer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            '
        ]);
    } catch (Exception $e) {
        $session_services['set_flash_message']('error', 'Une erreur est survenue');
        redirect('?page=apprenants');
    }
}

function import_apprenants_process() {
    global $model, $session_services;
    
    try {
        check_auth();

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Veuillez sélectionner un fichier Excel valide');
        }

        require_once __DIR__ . '/../services/import.service.php';
        $data = \App\Services\import_excel_data($_FILES['file']);

        // Ignorer la première ligne (en-têtes)
        array_shift($data);
        
        $success_retained = 0;
        $success_waiting = 0;
        $errors = [];
        $existing_emails = [];
        $existing_phones = [];

        foreach ($data as $index => $row) {
            if (array_filter($row)) { // Ignorer les lignes vides
                try {
                    $validation = validate_import_data($row);
                    $is_duplicate = false;

                    // Vérifier les doublons pour l'email s'il existe
                    if (!empty($row[2])) {
                        if (in_array($row[2], $existing_emails) || $model['check_apprenant_exists']($row[2], null)) {
                            $errors[] = "Ligne " . ($index + 2) . " : Email déjà utilisé";
                            $is_duplicate = true;
                        }
                        $existing_emails[] = $row[2];
                    }

                    // Vérifier les doublons pour le téléphone s'il existe
                    if (!empty($row[3])) {
                        if (in_array($row[3], $existing_phones) || $model['check_apprenant_exists'](null, $row[3])) {
                            $errors[] = "Ligne " . ($index + 2) . " : Téléphone déjà utilisé";
                            $is_duplicate = true;
                        }
                        $existing_phones[] = $row[3];
                    }

                    // Préparer les données de l'apprenant
                    $apprenant_data = [
                        'prenom' => $row[0] ?? '',
                        'nom' => $row[1] ?? '',
                        'email' => $row[2] ?? '',
                        'telephone' => $row[3] ?? '',
                        'adresse' => $row[4] ?? '',
                        'date_naissance' => $row[5] ?? '',
                        'lieu_naissance' => $row[6] ?? '',
                        'referentiel_id' => $row[7] ?? '',
                        'status' => 'en_attente',
                        'imported_data' => array_filter($row), // Sauvegarder les données importées
                        'missing_fields' => $validation['missing_fields'] // Sauvegarder les champs manquants
                    ];

                    if (!$is_duplicate) {
                        if ($validation['valid']) {
                            // Apprenant complet -> liste retenue
                            $apprenant_data['status'] = 'actif';
                            $result = $model['add_apprenant']($apprenant_data);
                            if ($result['success']) {
                                $success_retained++;
                            }
                        } else {
                            // Apprenant incomplet -> liste d'attente
                            $result = $model['add_apprenant']($apprenant_data);
                            if ($result['success']) {
                                $success_waiting++;
                                $missing = implode(', ', $validation['missing_fields']);
                                $errors[] = "Ligne " . ($index + 2) . " : En attente - Champs manquants : " . $missing;
                            }
                        }
                    }

                } catch (\Exception $e) {
                    $errors[] = "Ligne " . ($index + 2) . " : " . $e->getMessage();
                }
            }
        }

        // Message de résultat
        $message = [];
        if ($success_retained > 0) {
            $message[] = "$success_retained apprenants ajoutés à la liste des retenus";
        }
        if ($success_waiting > 0) {
            $message[] = "$success_waiting apprenants ajoutés à la liste d'attente";
        }
        
        if (!empty($message)) {
            $session_services['set_flash_message']('success', implode(', ', $message));
        }
        
        if (!empty($errors)) {
            $session_services['set_flash_message']('warning', implode('<br>', $errors));
        }

    } catch (\Exception $e) {
        $session_services['set_flash_message']('error', $e->getMessage());
    }
    
    redirect('?page=import-apprenants');
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

function download_template() {
    try {
        require_once __DIR__ . '/../../vendor/autoload.php';
        require_once __DIR__ . '/../services/excel.service.php';
        
        // Générer le template
        $spreadsheet = \App\Services\generate_excel_template();
        
        // Headers pour le téléchargement
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="modele_import_apprenants.xlsx"');
        header('Cache-Control: max-age=0');
        header('Expires: 0');
        
        // Créer le writer et envoyer le fichier
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
        
    } catch (\Exception $e) {
        error_log('Erreur lors de la génération du template: ' . $e->getMessage());
        redirect('?page=import-apprenants');
    }
}