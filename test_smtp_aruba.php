<?php
// FILE: test_smtp_aruba.php
// Mettere nella root del progetto (g:\htdocs\amministratoriprof\)
// Accedere via browser: http://localhost/amministratoriprof/test_smtp_aruba.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Inclusione manuale classi PHPMailer (adattato alla tua struttura)
$baseDir = __DIR__;
require $baseDir . '/vendor/phpmailer/phpmailer/src/Exception.php';
require $baseDir . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require $baseDir . '/vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// 2. Recupero configurazione dal DB (opzionale, precompila il form)
$dbConfig = [];
try {
    $configFile = $baseDir . '/app/config.php';
    if (file_exists($configFile)) {
        $cfg = require $configFile;
        $dsn = "mysql:host={$cfg['db']['host']};port={$cfg['db']['port']};dbname={$cfg['db']['name']};charset={$cfg['db']['charset']}";
        $pdo = new PDO($dsn, $cfg['db']['user'], $cfg['db']['pass']);
        $stmt = $pdo->query("SELECT * FROM email_settings ORDER BY id DESC LIMIT 1");
        $dbConfig = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
} catch (Exception $e) {
    echo "<div style='color:orange'>Warning: Impossibile leggere config dal DB: " . $e->getMessage() . "</div>";
}

// Valori di default o dal DB
$host = $_POST['host'] ?? ($dbConfig['smtp_host'] ?? 'smtps.aruba.it');
$port = $_POST['port'] ?? ($dbConfig['smtp_port'] ?? 465);
$user = $_POST['user'] ?? ($dbConfig['username'] ?? '');
$pass = $_POST['pass'] ?? ($dbConfig['password'] ?? ''); // Non mostrare password del DB per sicurezza se vuoi, ma per debug locale è comodo
$from = $_POST['from'] ?? ($dbConfig['smtp_from_email'] ?? $user); // Default from = user
$to   = $_POST['to']   ?? '';

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Test SMTP Aruba Standalone</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input[type="text"], input[type="password"], input[type="number"] { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 10px 20px; background: #007bff; color: #fff; border: none; cursor: pointer; font-size: 16px; border-radius: 4px; }
        button:hover { background: #0056b3; }
        .debug-box { background: #222; color: #0f0; padding: 15px; margin-top: 20px; overflow-x: auto; font-family: monospace; border-radius: 4px; }
        .success { color: green; border: 1px solid green; padding: 10px; background: #e8f5e9; margin-top: 20px; }
        .error { color: red; border: 1px solid red; padding: 10px; background: #ffebee; margin-top: 20px; }
        .note { font-size: 0.9em; color: #666; margin-top: 5px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Test SMTP Aruba (Standalone)</h1>
    <p>Questo script testa l'invio diretto bypassando il framework dell'app.</p>

    <form method="post">
        <label>SMTP Host</label>
        <input type="text" name="host" value="<?php echo htmlspecialchars($host); ?>">
        <div class="note">Per Aruba SSL usa: <b>smtps.aruba.it</b></div>

        <label>Porta</label>
        <input type="number" name="port" value="<?php echo htmlspecialchars($port); ?>">
        <div class="note">Per Aruba SSL usa: <b>465</b></div>

        <label>Username (Email Completa)</label>
        <input type="text" name="user" value="<?php echo htmlspecialchars($user); ?>" placeholder="nome@tuodominio.it">

        <label>Password</label>
        <input type="text" name="pass" value="<?php echo htmlspecialchars($pass); ?>">

        <label>Mittente (From)</label>
        <input type="text" name="from" value="<?php echo htmlspecialchars($from); ?>">
        <div class="note">⚠️ DEVE essere uguale allo Username per Aruba!</div>

        <label>Destinatario (A chi inviare il test)</label>
        <input type="text" name="to" value="<?php echo htmlspecialchars($to); ?>" required placeholder="destinatario@esempio.com">

        <button type="submit" name="send_test">🚀 Invia Email di Test</button>
    </form>

    <?php
    if (isset($_POST['send_test'])) {
        echo "<hr>";
        $mail = new PHPMailer(true);

        try {
            // Configurazione
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            
            // Auto-detect secure mode
            if ($port == 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($port == 587) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = ''; // Nessuna o auto
                $mail->SMTPAutoTLS = false; // Forza off se vuoi testare 25
            }
            
            $mail->Port       = $port;

            // Debug
            $mail->SMTPDebug  = 3; // Debug livello connessione + client
            $mail->Debugoutput = function($str, $level) {
                // Filtra password dal log per sicurezza visiva
                $str = preg_replace('/PASS\s+.*/', 'PASS *****', $str); 
                echo "<div class='debug-line'>$str</div>";
            };

            echo "<div class='debug-box'><strong>--- INIZIO LOG SMTP ---</strong><br>";
            
            // Impostazione email
            $mail->setFrom($from, 'Test SMTP Script');
            $mail->addAddress($to);
            $mail->Subject = 'Test SMTP Standalone - ' . date('Y-m-d H:i:s');
            $mail->Body    = "Questa è una email di test inviata dallo script standalone.\n\nHost: $host\nPorta: $port\nUser: $user\nFrom: $from";

            $mail->send();

            echo "<strong>--- FINE LOG SMTP ---</strong></div>";
            echo "<h2 class='success'>✅ EMAIL INVIATA CON SUCCESSO!</h2>";
            echo "<p>Controlla la casella <b>$to</b> (anche nello SPAM).</p>";

        } catch (Exception $e) {
            echo "<strong>--- FINE LOG SMTP ---</strong></div>";
            echo "<h2 class='error'>❌ ERRORE INVIO</h2>";
            echo "<p>Errore PHPMailer: " . htmlspecialchars($mail->ErrorInfo) . "</p>";
        }
    }
    ?>
</div>

</body>
</html>
