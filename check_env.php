<?php
// Script di diagnosi per ambiente di produzione
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnostica Ambiente</h1>";

// 1. Check PHP Version
echo "<h2>1. Versione PHP</h2>";
echo "PHP Version: " . phpversion() . "<br>";
if (version_compare(phpversion(), '7.4.0', '<')) {
    echo "<strong style='color:red'>ATTENZIONE: Versione PHP obsoleta. Consigliato 8.0+</strong><br>";
} else {
    echo "<span style='color:green'>OK</span><br>";
}

// 2. Check Permissions
echo "<h2>2. Permessi Cartelle</h2>";
$folders = [
    'storage/documents' => __DIR__ . '/storage/documents',
    'storage/logs' => __DIR__ . '/storage/logs',
    'public/uploads' => __DIR__ . '/public/uploads'
];

foreach ($folders as $name => $path) {
    if (!file_exists($path)) {
        // Tenta di creare
        @mkdir($path, 0775, true);
    }
    
    if (is_writable($path)) {
        echo "$name: <span style='color:green'>Scrivibile</span><br>";
    } else {
        echo "$name: <strong style='color:red'>NON Scrivibile</strong> (Esegui: <code>chmod -R 775 $name</code>)<br>";
    }
}

// 3. Check Database Connection
echo "<h2>3. Connessione Database</h2>";
$configFile = __DIR__ . '/app/config.php';
if (!file_exists($configFile)) {
    echo "<strong style='color:red'>ERRORE: File app/config.php mancante!</strong><br>";
    echo "Rinomina <code>app/config.production.sample.php</code> in <code>app/config.php</code> e inserisci i dati corretti.<br>";
} else {
    $c = require $configFile;
    echo "Host: " . htmlspecialchars($c['db']['host']) . "<br>";
    echo "DB Name: " . htmlspecialchars($c['db']['name']) . "<br>";
    echo "User: " . htmlspecialchars($c['db']['user']) . "<br>";
    
    try {
        $dsn = "mysql:host={$c['db']['host']};port={$c['db']['port']};dbname={$c['db']['name']};charset={$c['db']['charset']}";
        $pdo = new PDO($dsn, $c['db']['user'], $c['db']['pass']);
        echo "<strong style='color:green'>Connessione al Database RIUSCITA!</strong><br>";
        
        // Check tables
        $res = $pdo->query("SHOW TABLES");
        $tables = $res->fetchAll(PDO::FETCH_COLUMN);
        echo "Tabelle trovate: " . count($tables) . "<br>";
        if (count($tables) == 0) {
            echo "<strong style='color:orange'>ATTENZIONE: Il database è vuoto. Importa il file database/schema.sql</strong><br>";
        }
        
    } catch (PDOException $e) {
        echo "<strong style='color:red'>ERRORE CONNESSIONE: " . $e->getMessage() . "</strong><br>";
    }
}

// 4. Check Vendor
echo "<h2>4. Librerie (Vendor)</h2>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "<span style='color:green'>OK (vendor presente)</span><br>";
} else {
    echo "<strong style='color:red'>ERRORE: Cartella vendor mancante. Esegui 'composer install' sul server o carica la cartella vendor.</strong><br>";
}
