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
use PHPMailer\PHPMailer\SMTP;

class EmailService {

    public static function send($to, $name, $subject, $body, $attachments = [], $returnDebug = false) {
        $pdo = DB::conn();
        $settings = $pdo->query("SELECT * FROM email_settings ORDER BY id DESC LIMIT 1")->fetch();

        if (!$settings) {
            return ['success' => false, 'error' => 'Configurazione email non trovata'];
        }

        $mail = new PHPMailer(true);
        $debugLog = '';

        try {
            // Server settings
            $mail->SMTPDebug = 2; // SMTP::DEBUG_SERVER non sempre è caricato se SMTP non è use'd correttamente, 2 è safe
            $mail->Debugoutput = function($str, $level) use (&$debugLog) {
                $debugLog .= date('Y-m-d H:i:s') . " [DEBUG] $str\n";
                // error_log("SMTP DEBUG: $str"); // Disabilito error_log se catturo
            };

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
            
            // Fix per Aruba: Il mittente DEVE essere uguale all'utente autenticato
            // Se l'utente ha impostato un'email mittente diversa, Aruba potrebbe bloccare l'invio.
            // Aggiungiamo un warning nel log di debug se differiscono.
            if ($fromEmail !== $settings['username'] && strpos($settings['smtp_host'], 'aruba') !== false) {
                 $debugLog .= date('Y-m-d H:i:s') . " [WARNING] Sender address '$fromEmail' differs from SMTP username '{$settings['username']}'. Aruba might block this.\n";
            }
            
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
            return ['success' => true, 'debug' => $returnDebug ? $debugLog : null];
        } catch (Exception $e) {
            error_log("MAILER ERROR: " . $mail->ErrorInfo); // Log errore finale
            return ['success' => false, 'error' => "Errore invio email: {$mail->ErrorInfo}", 'debug' => $returnDebug ? $debugLog : null];
        }
    }
}
