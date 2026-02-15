<?php
require_once __DIR__ . '/app/core/DB.php';
use App\Core\DB;

try {
    $pdo = DB::conn();
    echo "Controllo colonna public_url in settings...\n";
    
    // Controlla se la colonna esiste
    $cols = $pdo->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('public_url', $cols)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN public_url VARCHAR(255) DEFAULT NULL AFTER association_name");
        echo "Colonna public_url aggiunta.\n";
    } else {
        echo "Colonna public_url già presente.\n";
    }
    
    echo "Migrazione completata.\n";
    
} catch (Exception $e) {
    echo "Errore: " . $e->getMessage() . "\n";
    exit(1);
}
