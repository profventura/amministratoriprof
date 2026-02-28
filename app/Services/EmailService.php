<?php
namespace App\Services;

use App\Core\DB;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {

    public static function send($to, $name, $subject, $body, $attachments = []) {
        $pdo = DB::conn();
        $settings = $pdo->query("SELECT * FROM email_settings ORDER BY id DESC LIMIT 1")->fetch();

        if (!$settings) {
            return ['success' => false, 'error' => 'Configurazione email non trovata'];
        }

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $settings['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $settings['username'];
            $mail->Password   = $settings['password'];
            $mail->SMTPSecure = $settings['smtp_secure']; // 'tls' or 'ssl'
            $mail->Port       = (int)$settings['smtp_port'];
            
            // Mittente
            $fromEmail = $settings['smtp_from_email'] ?: $settings['username'];
            $fromName = $settings['smtp_from_name'] ?: 'Amministratore';
            $mail->setFrom($fromEmail, $fromName);

            // Destinatario
            $mail->addAddress($to, $name);

            // CC / BCC
            if (!empty($settings['smtp_cc'])) {
                foreach(explode(',', $settings['smtp_cc']) as $cc) {
                    if (trim($cc)) $mail->addCC(trim($cc));
                }
            }
            if (!empty($settings['smtp_bcc'])) {
                foreach(explode(',', $settings['smtp_bcc']) as $bcc) {
                    if (trim($bcc)) $mail->addBCC(trim($bcc));
                }
            }

            // Allegati
            foreach ($attachments as $att) {
                if (file_exists($att['path'])) {
                    $mail->addAttachment($att['path'], $att['name'] ?? '');
                }
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => "Errore invio email: {$mail->ErrorInfo}"];
        }
    }
}
