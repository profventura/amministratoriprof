<?php
// Questo script va copiato e caricato sul server remoto (nella root o dove gira l'app)
// Esegui: php migration_remote_split_billing.php

// Configurazione DB (modifica se necessario o assicurati che config.php esista)
if (file_exists('config.php')) {
    require 'config.php';
} elseif (file_exists('../config.php')) {
    require '../config.php';
}

if (!defined('DB_HOST')) die("Configurazione DB non trovata.\n");

// Connessione PDO manuale se App\Core\DB non disponibile, o includiamo i file
// Per semplicità facciamo una connessione diretta qui
try {
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Errore connessione DB: " . $e->getMessage() . "\n");
}

echo "Inizio migrazione separazione CF/PIVA Fatturazione...\n";

try {
    // 1. Aggiunta colonne
    $cols = [
        'billing_cf' => "VARCHAR(32) NULL",
        'billing_piva' => "VARCHAR(32) NULL"
    ];
    
    // Controlla colonne esistenti
    $existing = $pdo->query("SHOW COLUMNS FROM members")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($cols as $col => $def) {
        if (!in_array($col, $existing)) {
            try {
                // Tenta di aggiungere dopo billing_cf_piva se esiste, altrimenti dopo tax_code
                $after = in_array('billing_cf_piva', $existing) ? 'billing_cf_piva' : 'tax_code';
                $pdo->exec("ALTER TABLE members ADD COLUMN $col $def AFTER $after");
                echo "OK: Aggiunta colonna $col\n";
            } catch (Exception $e) {
                echo "ERR: $col - " . $e->getMessage() . "\n";
            }
        } else {
            echo "SKIP: Colonna $col esiste gia'\n";
        }
    }
    
    // 2. Migrazione dati (se billing_cf_piva esiste)
    if (in_array('billing_cf_piva', $existing)) {
        echo "Migrazione dati da billing_cf_piva...\n";
        $stmt = $pdo->query("SELECT id, billing_cf_piva FROM members WHERE billing_cf_piva IS NOT NULL AND billing_cf_piva != ''");
        while ($row = $stmt->fetch()) {
            $val = trim($row['billing_cf_piva']);
            // Euristica semplice: numerico 11 cifre -> PIVA, altrimenti CF
            $isVat = (is_numeric($val) && strlen($val) === 11);
            
            if ($isVat) {
                // Controlla se billing_piva è vuoto prima di sovrascrivere (opzionale, ma sicuro)
                $pdo->prepare("UPDATE members SET billing_piva = ? WHERE id = ? AND (billing_piva IS NULL OR billing_piva = '')")->execute([$val, $row['id']]);
            } else {
                $pdo->prepare("UPDATE members SET billing_cf = ? WHERE id = ? AND (billing_cf IS NULL OR billing_cf = '')")->execute([$val, $row['id']]);
            }
        }
        echo "Migrazione dati completata.\n";
        
        // 3. Drop colonna vecchia (commentare se si vuole mantenere per sicurezza per ora)
        // $pdo->exec("ALTER TABLE members DROP COLUMN billing_cf_piva");
        // echo "OK: Rimossa colonna billing_cf_piva\n";
        echo "NOTA: La colonna 'billing_cf_piva' NON e' stata rimossa automaticamente per sicurezza. Rimuovila manualmente se tutto ok.\n";
        echo "SQL per rimuovere: ALTER TABLE members DROP COLUMN billing_cf_piva;\n";
    }
    
    echo "Migrazione completata con successo.\n";

} catch (Exception $e) {
    echo "Errore Fatale: " . $e->getMessage() . "\n";
}
