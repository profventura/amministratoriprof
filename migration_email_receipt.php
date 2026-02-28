<?php
require_once __DIR__ . '/app/core/DB.php';
use App\Core\DB;

try {
    $pdo = DB::conn();
    echo "Controllo e aggiunta campi email ricevute...\n";
    
    // Controlla colonne esistenti
    $cols = $pdo->query("SHOW COLUMNS FROM email_settings")->fetchAll(PDO::FETCH_COLUMN);
    
    // Email Ricevute Subject
    if (!in_array('email_receipt_subject', $cols)) {
        $pdo->exec("ALTER TABLE email_settings ADD COLUMN email_receipt_subject VARCHAR(255) DEFAULT 'Ricevuta Pagamento' AFTER email_dm_certificate_body");
        echo "Aggiunto email_receipt_subject.\n";
    }
    
    // Email Ricevute Body
    if (!in_array('email_receipt_body', $cols)) {
        $pdo->exec("ALTER TABLE email_settings ADD COLUMN email_receipt_body TEXT AFTER email_receipt_subject");
        echo "Aggiunto email_receipt_body.\n";
    }
    
    echo "Migrazione completata.\n";
    
} catch (Exception $e) {
    echo "Errore: " . $e->getMessage() . "\n";
    exit(1);
}
