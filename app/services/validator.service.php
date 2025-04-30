<?php

namespace App\Services;

// Regroupement des fonctions de validation
$validator_services = [
    'is_empty' => function ($value) {
        return empty(trim($value));
    },
    
    'min_length' => function ($value, $min) {
        return strlen(trim($value)) >= $min;
    },
    
    'max_length' => function ($value, $max) {
        return strlen(trim($value)) <= $max;
    },
    
    'is_email' => function ($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    },
    
    'is_valid_image' => function ($file) {
        // Vérifier si le fichier est une image valide (JPG ou PNG) et sa taille
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return false;
        }
        
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 2 * 1024 * 1024; // 2MB en octets
        
        $file_info = getimagesize($file['tmp_name']);
        $file_type = $file_info ? $file_info['mime'] : '';
        
        return in_array($file_type, $allowed_types) && $file['size'] <= $max_size;
    },
    
    // Nouvelle fonction pour valider une date au format JJ-MM-AAAA
    'validate_french_date' => function($date) {
        // Vérifier le format JJ-MM-AAAA
        if (!preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $date, $matches)) {
            return false;
        }
        
        $day = (int)$matches[1];
        $month = (int)$matches[2];
        $year = (int)$matches[3];
        
        // Vérifier si la date est valide
        return checkdate($month, $day, $year);
    },
    
    // Fonction pour comparer deux dates au format JJ-MM-AAAA
    'compare_french_dates' => function($date1, $date2) {
        // Convertir en timestamp pour la comparaison
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $date1, $matches1) && 
            preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $date2, $matches2)) {
            
            $day1 = (int)$matches1[1];
            $month1 = (int)$matches1[2];
            $year1 = (int)$matches1[3];
            
            $day2 = (int)$matches2[1];
            $month2 = (int)$matches2[2];
            $year2 = (int)$matches2[3];
            
            $timestamp1 = mktime(0, 0, 0, $month1, $day1, $year1);
            $timestamp2 = mktime(0, 0, 0, $month2, $day2, $year2);
            
            return $timestamp2 > $timestamp1; // true si date2 est postérieure à date1
        }
        
        return false; // Si l'une des dates n'est pas valide
    },

    'validate_form' => function ($data, $rules) {
        $errors = [];
        
        $validate_rule = function($field, $rule, $rule_value, $data, &$errors) {
            $result = match($rule) {
                'required' => $rule_value && empty(trim($data[$field])) 
                    ? ["Le champ est obligatoire"] : [],
                'min_length' => !empty($data[$field]) && strlen(trim($data[$field])) < $rule_value 
                    ? ["Le champ doit contenir au moins $rule_value caractères"] : [],
                'max_length' => !empty($data[$field]) && strlen(trim($data[$field])) > $rule_value 
                    ? ["Le champ ne doit pas dépasser $rule_value caractères"] : [],
                'email' => $rule_value && !empty($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL) 
                    ? ["Email invalide"] : [],
                default => []
            };
            
            if (!empty($result)) {
                if (!isset($errors[$field])) {
                    $errors[$field] = [];
                }
                $errors[$field] = array_merge($errors[$field], $result);
            }
        };
        
        $process_field = function($field, $field_rules) use ($data, &$errors, $validate_rule) {
            $rule_keys = array_keys($field_rules);
            array_map(function($rule) use ($field, $field_rules, $data, &$errors, $validate_rule) {
                $validate_rule($field, $rule, $field_rules[$rule], $data, $errors);
            }, $rule_keys);
        };
        
        $fields = array_keys($rules);
        array_map(function($field) use ($rules, $process_field) {
            $process_field($field, $rules[$field]);
        }, $fields);
        
        return $errors;
    },
    
    'validate_promotion' => function(array $post_data, array $files): array {
        global $validator_services;
        $errors = [];
        
        // Validation du nom (obligatoire et unique)
        if (empty($post_data['name'])) {
            $errors[] = 'Le nom de la promotion est requis';
        } else {
            global $model;
            if ($model['promotion_name_exists']($post_data['name'])) {
                $errors[] = 'Ce nom de promotion existe déjà';
            }
        }
        
        // Validation des dates (obligatoires)
        if (empty($post_data['date_debut'])) {
            $errors[] = 'La date de début est requise';
        } else {
            // Vérifier le format JJ-MM-AAAA
            if (!$validator_services['validate_french_date']($post_data['date_debut'])) {
                $errors[] = 'Format de date de début invalide. Utilisez JJ-MM-AAAA';
            }
        }
        
        if (empty($post_data['date_fin'])) {
            $errors[] = 'La date de fin est requise';
        } else {
            // Vérifier le format JJ-MM-AAAA
            if (!$validator_services['validate_french_date']($post_data['date_fin'])) {
                $errors[] = 'Format de date de fin invalide. Utilisez JJ-MM-AAAA';
            }
        }
        
        // Comparer les dates si les deux sont valides
        if (!empty($post_data['date_debut']) && !empty($post_data['date_fin']) && 
            $validator_services['validate_french_date']($post_data['date_debut']) && 
            $validator_services['validate_french_date']($post_data['date_fin'])) {
            
            if (!$validator_services['compare_french_dates']($post_data['date_debut'], $post_data['date_fin'])) {
                $errors[] = 'La date de fin doit être supérieure à la date de début';
            }
        }
        
        // Validation de l'image
        if (empty($files['image']['name'])) {
            $errors[] = 'L\'image de la promotion est requise';
        } else {
            $allowed_types = ['image/jpeg', 'image/png'];
            if (!in_array($files['image']['type'], $allowed_types)) {
                $errors[] = 'Le format de l\'image doit être JPG ou PNG';
            }
            
            if ($files['image']['size'] > 2 * 1024 * 1024) { // 2MB
                $errors[] = 'La taille de l\'image ne doit pas dépasser 2MB';
            }
        }
        
        // Validation des référentiels (au moins un requis)
        if (empty($post_data['referentiels'])) {
            $errors[] = 'Au moins un référentiel doit être sélectionné';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
];