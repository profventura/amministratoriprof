<?php
require_once __DIR__ . '/app/core/DB.php';
use App\Core\DB;

try {
    $pdo = DB::conn();
    echo "Aggiunta colonne username e password_hash a members...\n";
    
    // Controlla se le colonne esistono già
    $cols = $pdo->query("SHOW COLUMNS FROM members")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('username', $cols)) {
        $pdo->exec("ALTER TABLE members ADD COLUMN username VARCHAR(100) DEFAULT NULL AFTER email");
        $pdo->exec("ALTER TABLE members ADD UNIQUE KEY uq_members_username (username)");
        echo "Colonna username aggiunta.\n";
    } else {
        echo "Colonna username già presente.\n";
    }
    
    if (!in_array('password_hash', $cols)) {
        $pdo->exec("ALTER TABLE members ADD COLUMN password_hash VARCHAR(255) DEFAULT NULL AFTER username");
        echo "Colonna password_hash aggiunta.\n";
    } else {
        echo "Colonna password_hash già presente.\n";
    }
    
    echo "Migrazione completata.\n";
    
} catch (Exception $e) {
    echo "Errore: " . $e->getMessage() . "\n";
    exit(1);
}
