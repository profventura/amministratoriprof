<?php
/*
  File: PublicAuth.php
  Scopo: Gestione autenticazione area pubblica (Soci).
  Spiegazione: Separa l'autenticazione degli amministratori da quella dei soci.
*/
namespace App\Core;
use App\Core\DB;

class PublicAuth {
    // Ritorna i dati del socio corrente (o null se non loggato)
    public static function user() { return $_SESSION['member_user'] ?? null; }
    
    // Verifica se esiste una sessione attiva per il socio
    public static function check() { return isset($_SESSION['member_user']); }
    
    // Impone l’autenticazione: se non loggato, reindirizza al login soci
    public static function require() { 
        if (!self::check()) { 
            Helpers::redirect('/portal/login'); 
        } 
    }
    
    /*
      Metodo: login
      Parametri: username, password
      Funzione: Autentica il socio, inizializza la sessione.
      Ritorno: true/false
    */
    public static function login($username, $password) {
        $pdo = DB::conn();
        // Cerca socio attivo per username
        $stmt = $pdo->prepare('SELECT * FROM members WHERE username = ? AND deleted_at IS NULL AND status = "active" LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        // Verifica password
        if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
            // Rigenera ID sessione per sicurezza (evita session fixation)
            session_regenerate_id(true);
            
            $_SESSION['member_user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email']
            ];
            return true;
        }
        
        return false;
    }
    
    // Effettua logout e pulisce sessione socio
    public static function logout() {
        unset($_SESSION['member_user']);
    }
    
    // Helper per ottenere l'ID del socio loggato
    public static function id() {
        return $_SESSION['member_user']['id'] ?? null;
    }
}
