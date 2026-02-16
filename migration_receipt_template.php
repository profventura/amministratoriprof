<?php
require_once __DIR__ . '/app/core/DB.php';
use App\Core\DB;

try {
    $pdo = DB::conn();
    echo "Controllo e aggiunta campi per template ricevute in settings...\n";
    
    // Controlla se la colonna esiste
    $cols = $pdo->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN);
    
    // Campi base template
    if (!in_array('receipt_template_path', $cols)) {
        $pdo->exec("ALTER TABLE settings ADD COLUMN receipt_template_path VARCHAR(255) DEFAULT NULL AFTER public_url");
        echo "Aggiunto receipt_template_path.\n";
    }

    // Campi Timbro: Numero, Data, Nome, Indirizzo, CF, Importo, Causale
    $fields = [
        'receipt_number', 'receipt_date', 'member_name', 'member_address', 'member_cf', 'amount', 'description'
    ];
    
    foreach ($fields as $f) {
        $prefix = "receipt_stamp_{$f}";
        if (!in_array("{$prefix}_x", $cols)) {
            $pdo->exec("ALTER TABLE settings 
                ADD COLUMN {$prefix}_x INT DEFAULT 0,
                ADD COLUMN {$prefix}_y INT DEFAULT 0,
                ADD COLUMN {$prefix}_font_size INT DEFAULT 12,
                ADD COLUMN {$prefix}_font_family VARCHAR(50) DEFAULT 'Arial',
                ADD COLUMN {$prefix}_color VARCHAR(7) DEFAULT '#000000',
                ADD COLUMN {$prefix}_bold TINYINT(1) DEFAULT 0
            ");
            echo "Aggiunti campi per {$f}.\n";
        }
    }
    
    echo "Migrazione completata.\n";
    
} catch (Exception $e) {
    echo "Errore: " . $e->getMessage() . "\n";
    exit(1);
}
