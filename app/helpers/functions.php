<?php
function getBadgeClass($referentiel) {
    $classes = [
        'DEV WEB/MOBILE' => 'dev-web',
        'REF DIG' => 'ref-dig',
        'DEV DATA' => 'dev-data',
        'AWS' => 'aws',
        'HACKEUSE' => 'hackeuse'
    ];
    
    return $classes[$referentiel] ?? 'dev-web';
}
function hasIncompleteInfo($apprenant) {
    $requiredFields = [
        'photo',
        'date_naissance',
        'lieu_naissance',
        'adresse',
        'telephone',
        'email',
        'referentiel_id'
    ];

    foreach ($requiredFields as $field) {
        if (!isset($apprenant[$field]) || empty($apprenant[$field])) {
            return true;
        }
    }

    return false;
}

function getMissingInfo($apprenant) {
    $missing = [];
    $fields = [
        'photo' => 'Photo',
        'date_naissance' => 'Date de naissance',
        'lieu_naissance' => 'Lieu de naissance',
        'adresse' => 'Adresse',
        'telephone' => 'Téléphone',
        'email' => 'Email',
        'referentiel_id' => 'Référentiel'
    ];

    foreach ($fields as $field => $label) {
        if (!isset($apprenant[$field]) || empty($apprenant[$field])) {
            $missing[] = $label;
        }
    }

    return $missing;
}