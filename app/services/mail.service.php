<?php

namespace App\Services;

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