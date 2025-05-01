<?php

namespace App\Services;

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail_config = require __DIR__ . '/../config/mail.config.php';

$mail_services = [
    'send_welcome_email' => function($user) use ($mail_config) {
        try {
            $mail = new PHPMailer(true);
            
            // Configuration SMTP existante
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'error_log';
            $mail->isSMTP();
            $mail->Host = $mail_config['smtp']['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $mail_config['smtp']['username'];
            $mail->Password = $mail_config['smtp']['password'];
            $mail->SMTPSecure = $mail_config['smtp']['encryption'];
            $mail->Port = $mail_config['smtp']['port'];
            
            // Paramètres de l'email
            $mail->setFrom($mail_config['smtp']['from_email'], $mail_config['smtp']['from_name']);
            $mail->addAddress($user['email']);
            
            $mail->isHTML(true);
            $mail->Subject = 'Bienvenue à Sonatel Academy - Vos identifiants de connexion';
            
            // Corps du message avec les informations de connexion
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #ff7900;'>Bienvenue à Sonatel Academy !</h2>
                    
                    <p>Bonjour {$user['prenom']} {$user['nom']},</p>
                    
                    <p>Votre compte a été créé avec succès. Voici vos informations de connexion :</p>
                    
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <p><strong>Pour vous connecter, vous pouvez utiliser :</strong></p>
                        <ul>
                            <li>Votre matricule : {$user['matricule']}</li>
                            <li>OU votre email : {$user['email']}</li>
                        </ul>
                        <p><strong>Mot de passe temporaire :</strong> Sonatel@2024</p>
                    </div>
                    
                    <div style='background: #fff3e0; padding: 15px; border-left: 4px solid #ff7900; margin: 20px 0;'>
                        <p style='color: #e65100; margin: 0;'>
                            <strong>Important :</strong> Pour des raisons de sécurité, nous vous recommandons de changer votre mot de passe lors de votre première connexion.
                        </p>
                    </div>

                    <p>Cordialement,<br>L'équipe Sonatel Academy</p>
                    
                    <hr style='margin: 20px 0; border: none; border-top: 1px solid #eee;'>
                    <p style='color: #666; font-size: 12px;'>
                        Ce message est généré automatiquement, merci de ne pas y répondre.
                    </p>
                </div>
            ";
            
            // Version texte alternative pour les clients mail qui ne supportent pas l'HTML
            $mail->AltBody = "
                Bienvenue à Sonatel Academy !
                
                Bonjour {$user['prenom']} {$user['nom']},
                
                Votre compte a été créé avec succès. Voici vos informations de connexion :
                
                Pour vous connecter, vous pouvez utiliser :
                - Votre matricule : {$user['matricule']}
                - OU votre email : {$user['email']}
                
                Mot de passe temporaire : Sonatel@2024
                
                IMPORTANT : Pour des raisons de sécurité, nous vous recommandons de changer votre mot de passe lors de votre première connexion.
                
                Cordialement,
                L'équipe Sonatel Academy
            ";
            
            return $mail->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi d'email détaillée : " . $mail->ErrorInfo);
            return false;
        }
    }
];

function generate_password_token($user_id) {
    return hash('sha256', $user_id . time() . uniqid());
}

function send_welcome_email($user, $temp_password) {
    // Générer un token unique
    $token = generate_password_token($user['id']);
    
    // Construire le lien complet
    $change_password_link = "http://" . $_SERVER['HTTP_HOST'] . "?page=reset-password&token=" . $token;
    
    $html_content = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #ff7900; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
                .content { background-color: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 8px 8px; }
                .button { 
                    display: inline-block;
                    background-color: #ff7900;
                    color: white;
                    padding: 12px 24px;
                    text-decoration: none;
                    border-radius: 4px;
                    margin: 20px 0;
                }
                .warning { 
                    background-color: #fff3e0;
                    border-left: 4px solid #ff7900;
                    padding: 15px;
                    margin: 20px 0;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Bienvenue chez Sonatel Academy</h1>
                </div>
                <div class='content'>
                    <p>Bonjour " . htmlspecialchars($user['prenom']) . " " . htmlspecialchars($user['nom']) . ",</p>
                    
                    <p>Votre compte a été créé avec succès. Voici vos identifiants de connexion :</p>
                    
                    <div style='background: #f5f5f5; padding: 15px; border-radius: 4px;'>
                        <p><strong>Email :</strong> " . htmlspecialchars($user['email']) . "</p>
                        <p><strong>Mot de passe temporaire :</strong> " . htmlspecialchars($temp_password) . "</p>
                    </div>

                    <div class='warning'>
                        <p><strong>Important :</strong> Pour des raisons de sécurité, veuillez changer votre mot de passe en cliquant sur le bouton ci-dessous.</p>
                    </div>

                    <center>
                        <a href='" . $change_password_link . "' class='button' style='color: white; text-decoration: none;'>
                            Changer mon mot de passe
                        </a>
                    </center>

                    <p style='margin-top: 30px;'>
                        Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :<br>
                        <small>" . $change_password_link . "</small>
                    </p>

                    <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                        Cordialement,<br>
                        L'équipe Sonatel Academy
                    </p>
                </div>
            </div>
        </body>
        </html>
    ";
    
    // En-têtes pour l'email HTML
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Sonatel Academy <no-reply@sonatel-academy.sn>\r\n";
    
    // Sauvegarder le token dans les données de l'utilisateur
    global $model;
    $data = $model['read_data']();
    
    // Trouver l'utilisateur et ajouter le token
    foreach ($data['users'] as &$u) {
        if ($u['id'] === $user['id']) {
            $u['reset_token'] = $token;
            $u['reset_expire'] = time() + 86400; // Valide pendant 24h
            break;
        }
    }
    
    // Sauvegarder les modifications
    $model['write_data']($data);
    
    return mail($user['email'], "Bienvenue chez Sonatel Academy", $html_content, $headers);
}

/**
 * Envoie les identifiants par email à un nouvel apprenant
 */
function send_apprenant_credentials($apprenant_data, $temp_password) {
    try {
        $config = require __DIR__ . '/../config/mail.config.php';
        $mail = new PHPMailer(true);

        // Configuration du serveur
        $mail->isSMTP();
        $mail->Host = $config['smtp']['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp']['username'];
        $mail->Password = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['encryption'];
        $mail->Port = $config['smtp']['port'];
        $mail->CharSet = 'UTF-8';

        // Destinataires
        $mail->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
        $mail->addAddress($apprenant_data['email']);

        // Générer l'URL de connexion
        $login_url = "http://" . $_SERVER['HTTP_HOST'] . "?page=login";

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Vos identifiants de connexion - Sonatel Academy';

        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #ff7900; color: white; padding: 20px; text-align: center; }
                .content { background: #fff; padding: 20px; border: 1px solid #ddd; }
                .credentials { 
                    background: #f5f5f5; 
                    padding: 15px; 
                    margin: 20px 0; 
                    border-radius: 4px;
                    border-left: 4px solid #ff7900;
                }
                .warning {
                    background: #fff3cd;
                    color: #856404;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 4px;
                    border-left: 4px solid #ffeeba;
                }
                .button {
                    display: inline-block;
                    background-color: #ff7900;
                    color: white !important;
                    padding: 12px 24px;
                    text-decoration: none;
                    border-radius: 4px;
                    margin: 20px 0;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Bienvenue à Sonatel Academy</h1>
                </div>
                <div class='content'>
                    <p>Bonjour {$apprenant_data['prenom']} {$apprenant_data['nom']},</p>
                    
                    <p>Votre compte a été créé avec succès. Voici vos informations de connexion :</p>
                    
                    <div class='credentials'>
                        <h3>Vos identifiants</h3>
                        <p><strong>Matricule :</strong> {$apprenant_data['matricule']}</p>
                        <p><strong>Email :</strong> {$apprenant_data['email']}</p>
                        <p><strong>Mot de passe temporaire :</strong> {$temp_password}</p>
                    </div>

                    <div class='warning'>
                        <h3>⚠️ Important - Action requise</h3>
                        <p>Pour des raisons de sécurité, vous devez changer votre mot de passe lors de votre première connexion.</p>
                        <p>Cette étape est obligatoire pour accéder à votre compte.</p>
                    </div>

                    <center>
                        <a href='{$login_url}' class='button'>
                            Se connecter maintenant
                        </a>
                    </center>

                    <p>Pour vous connecter, suivez ces étapes :</p>
                    <ol>
                        <li>Cliquez sur le bouton ci-dessus ou rendez-vous sur la page de connexion</li>
                        <li>Utilisez votre matricule ou email et le mot de passe temporaire</li>
                        <li>Vous serez automatiquement redirigé vers la page de changement de mot de passe</li>
                        <li>Choisissez un nouveau mot de passe sécurisé</li>
                    </ol>

                    <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                        Cordialement,<br>
                        L'équipe Sonatel Academy
                    </p>
                </div>
            </div>
        </body>
        </html>";

        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);

        return $mail->send();
    } catch (Exception $e) {
        error_log("Erreur d'envoi de mail: " . $e->getMessage());
        return false;
    }
}

/**
 * Crée le template HTML du mail
 */
function create_mail_template($apprenant, $temp_password, $login_url) {
    return "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Bienvenue à Sonatel Academy</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #ff7900; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
            .content { background: #fff; padding: 20px; border: 1px solid #ddd; }
            .credentials { background: #f5f5f5; padding: 15px; margin: 20px 0; border-radius: 4px; }
            .button { display: inline-block; background: #ff7900; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Bienvenue à Sonatel Academy</h1>
            </div>
            <div class='content'>
                <p>Bonjour " . htmlspecialchars($apprenant['prenom']) . " " . htmlspecialchars($apprenant['nom']) . ",</p>
                <p>Votre compte a été créé avec succès. Voici vos identifiants de connexion :</p>
                <div class='credentials'>
                    <p><strong>Email :</strong> " . htmlspecialchars($apprenant['email']) . "</p>
                    <p><strong>Matricule :</strong> " . htmlspecialchars($apprenant['matricule']) . "</p>
                    <p><strong>Mot de passe :</strong> " . htmlspecialchars($temp_password) . "</p>
                </div>
                <p><a href='" . $login_url . "' class='button' style='color: white;'>Se connecter</a></p>
            </div>
        </div>
    </body>
    </html>";
}