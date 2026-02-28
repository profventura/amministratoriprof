<?php
namespace App\Services;

use App\Core\DB;

// Se non usiamo l'autoloader di composer, includiamo manualmente i file
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    $vendorDir = dirname(__DIR__, 2) . '/vendor/phpmailer/phpmailer/src/';
    if (file_exists($vendorDir . 'Exception.php')) require_once $vendorDir . 'Exception.php';
    if (file_exists($vendorDir . 'PHPMailer.php')) require_once $vendorDir . 'PHPMailer.php';
    if (file_exists($vendorDir . 'SMTP.php')) require_once $vendorDir . 'SMTP.php';
}

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
            
            // Fix per Aruba: spesso richiede 'ssl' su porta 465 o 'tls' su 587
            // Se smtp_secure è vuoto ma porta è 465, forza ssl.
            $secure = $settings['smtp_secure'];
            if (empty($secure)) {
                if ($settings['smtp_port'] == 465) $secure = PHPMailer::ENCRYPTION_SMTPS;
                elseif ($settings['smtp_port'] == 587) $secure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mail->SMTPSecure = $secure; 
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
            $mail->Body    = nl2br($body); // Converte newlines in <br>
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => "Errore invio email: {$mail->ErrorInfo}"];
        }
    }
}
