<?php
require_once __DIR__ . '/app/core/DB.php';
use App\Core\DB;

try {
    $pdo = DB::conn();
    echo "Controllo e fix dati di autenticazione (username/password)...\n";
    
    // 1. Recupera tutti i membri attivi
    $stmt = $pdo->query("SELECT id, first_name, last_name, username, password_hash FROM members WHERE deleted_at IS NULL");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updatedCount = 0;
    
    foreach ($members as $m) {
        $needsUpdate = false;
        $updates = [];
        $params = [];
        
        // Genera Username se mancante
        if (empty($m['username'])) {
            $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $m['first_name']));
            $cleanSurname = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $m['last_name']));
            $baseUsername = substr($cleanName, 0, 1) . '.' . $cleanSurname;
            
            $username = $baseUsername;
            $counter = 1;
            while (true) {
                // Controlliamo esistenza su DB
                $chk = $pdo->prepare("SELECT id FROM members WHERE username = ? AND id <> ?");
                $chk->execute([$username, $m['id']]);
                if (!$chk->fetch()) break;
                
                $username = $baseUsername . $counter;
                $counter++;
            }
            
            $updates[] = "username = ?";
            $params[] = $username;
            $needsUpdate = true;
            echo "Socio ID {$m['id']}: Generato username '$username'\n";
        }

        // Genera Password se mancante
        if (empty($m['password_hash'])) {
            // Default password: 'password'
            $defaultPass = 'password'; 
            $hash = password_hash($defaultPass, PASSWORD_DEFAULT);
            
            $updates[] = "password_hash = ?";
            $params[] = $hash;
            $needsUpdate = true;
            echo "Socio ID {$m['id']}: Impostata password default 'password'\n";
        }
        
        if ($needsUpdate) {
            $params[] = $m['id'];
            $sql = "UPDATE members SET " . implode(', ', $updates) . " WHERE id = ?";
            $pdo->prepare($sql)->execute($params);
            $updatedCount++;
        }
    }
    
    echo "Operazione completata. Aggiornati $updatedCount soci.\n";
    
} catch (Exception $e) {
    echo "Errore: " . $e->getMessage() . "\n";
    exit(1);
}
