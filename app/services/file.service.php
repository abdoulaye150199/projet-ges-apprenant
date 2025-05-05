<?php
namespace App\Services;

$file_services = [
    'handle_upload' => function($file, $destination_dir = 'uploads') {
        // Vérification du fichier
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Vérification du type de fichier
        $allowed_types = ['image/jpeg', 'image/png'];
        if (!in_array($file['type'], $allowed_types)) {
            return null;
        }

        // Vérification de la taille (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return null;
        }

        // Création du dossier de destination
        $upload_dir = __DIR__ . '/../../public/' . $destination_dir;
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                return null;
            }
        }

        // Génération d'un nom de fichier unique
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $destination = $upload_dir . '/' . $new_filename;

        // Upload du fichier
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return null;
        }

        return $destination_dir . '/' . $new_filename;
    }
];

// Ajouter la fonction upload_profile_image après la définition de $file_services
$file_services['upload_profile_image'] = function($file) use ($file_services) {
    return $file_services['handle_upload']($file, 'uploads/profiles');
};

function handle_upload($file, $folder = 'profiles') {
    try {
        // Vérifier si le dossier existe, sinon le créer
        $upload_dir = __DIR__ . '/../../public/assets/images/' . $folder . '/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }

        // Vérifier le type de fichier
        $allowed = ['jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed)) {
            throw new Exception('Format de fichier non autorisé. Formats acceptés : JPG, PNG');
        }

        // Générer un nom unique pour le fichier
        $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $new_filename;
        
        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Retourner le chemin relatif par rapport au dossier public
            return 'assets/images/' . $folder . '/' . $new_filename;
        }
        
        throw new Exception('Erreur lors du téléchargement du fichier');
    } catch (Exception $e) {
        error_log('Erreur upload: ' . $e->getMessage());
        throw $e;
    }
}