<?php
function add_referentiel_modal_process() {
    global $model, $session_services, $error_messages, $success_messages;
    
    
    $user = check_profile(Enums\ADMIN);
    
    // Récupération des données du formulaire modal
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $promotion_id = $_POST['promotion_id'] ?? '';
    $tags = $_POST['tags'] ?? [];
    
    // Validation des données
    $errors = [];
    
    if (empty($name)) {
        $errors['name'] = 'Le nom du référentiel est obligatoire';
    } elseif ($model['referentiel_name_exists']($name)) {
        $errors['name'] = 'Ce nom de référentiel existe déjà';
    }
    
    if (empty($description)) {
        $errors['description'] = 'La description est obligatoire';
    }
    
    // S'il y a des erreurs, renvoyer un message d'erreur JSON
    if (!empty($errors)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Préparation des données du référentiel avec des valeurs par défaut pour les champs manquants
    $referentiel_data = [
        'name' => $name,
        'description' => $description,
        'capacite' => 30, // Valeur par défaut
        'sessions' => 10, // Valeur par défaut
        'image' => 'assets/images/default-referentiel.jpg', // Image par défaut
        'tags' => $tags // Stockage des tags choisis
    ];
    
    // Création du référentiel
    $result = $model['create_referentiel']($referentiel_data);
    
    if (!$result) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du référentiel']);
        exit;
    }
    
    // Si une promotion a été sélectionnée, affecter le référentiel à cette promotion
    if (!empty($promotion_id)) {
        $model['assign_referentiels_to_promotion']($promotion_id, [$result['id']]);
    }
    
    // Renvoyer un message de succès
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Référentiel ajouté avec succès']);
    exit;
}