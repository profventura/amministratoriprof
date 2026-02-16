<?php
require_once __DIR__ . '/app/core/DB.php';
use App\Core\DB;

try {
    $pdo = DB::conn();
    echo "Controllo e aggiunta campi orientamento template...\n";
    
    // Controlla colonne esistenti
    $cols = $pdo->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN);
    
    // Certificati
    if (!in_array('certificate_orientation', $cols)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN certificate_orientation VARCHAR(1) DEFAULT 'P' AFTER membership_certificate_template_docx_path");
        echo "Aggiunto certificate_orientation.\n";
    }
    
    // Attestati DM
    if (!in_array('dm_certificate_orientation', $cols)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN dm_certificate_orientation VARCHAR(1) DEFAULT 'L' AFTER dm_certificate_template_docx_path");
        echo "Aggiunto dm_certificate_orientation.\n";
    }
    
    // Ricevute
    if (!in_array('receipt_orientation', $cols)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN receipt_orientation VARCHAR(1) DEFAULT 'P' AFTER receipt_template_path");
        echo "Aggiunto receipt_orientation.\n";
    }
    
    echo "Migrazione completata.\n";
    
} catch (Exception $e) {
    echo "Errore: " . $e->getMessage() . "\n";
    exit(1);
}
