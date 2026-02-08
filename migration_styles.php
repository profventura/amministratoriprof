<?php
// Carico manualmente il file DB e la configurazione
require_once __DIR__ . '/app/core/DB.php';
use App\Core\DB;

echo "Aggiornamento DB per stili PDF...\n";

try {
    $pdo = DB::conn();
    
    // Campi da aggiungere per ogni placeholder: x, y, size, color, font
    // Placeholder: name, number, date, year
    $fields = ['name', 'number', 'date', 'year'];
    $props = [
        'x' => 'INT DEFAULT 0', 
        'y' => 'INT DEFAULT 0', 
        'font_size' => 'INT DEFAULT 12', 
        'color' => "VARCHAR(7) DEFAULT '#000000'", 
        'font_family' => "VARCHAR(20) DEFAULT 'Helvetica'"
    ];

    foreach ($fields as $f) {
        foreach ($props as $p => $def) {
            $colName = "certificate_stamp_{$f}_{$p}";
            try {
                $pdo->query("SELECT $colName FROM settings LIMIT 1");
                // echo "Colonna $colName esistente.\n";
            } catch (PDOException $e) {
                echo "Aggiungo colonna: $colName\n";
                $pdo->exec("ALTER TABLE settings ADD COLUMN $colName $def");
            }
        }
    }
    
    echo "Migrazione completata.\n";

} catch (Exception $e) {
    echo "Errore: " . $e->getMessage() . "\n";
}
