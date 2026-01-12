<?php
/*
  File: Auth.php
  Scopo: Gestione autenticazione utenti (studenti/admin), login/logout e controllo permessi.
  Spiegazione: Fornisce metodi statici per verificare l’utente loggato, effettuare login,
  registrare gli accessi nei log e applicare regole di autorizzazione.
*/
namespace App\Core;
use App\Core\DB;
class Auth {
  // Ritorna i dati dell’utente corrente (o null se non loggato)
  public static function user() { return $_SESSION['user'] ?? null; }
  // Verifica se esiste una sessione attiva
  public static function check() { return isset($_SESSION['user']); }
  // Impone l’autenticazione: se non loggato, reindirizza al login
  public static function require() { if (!self::check()) { Helpers::redirect('/login'); } }
  /*
    Metodo: login
    Parametri: username, password
    Funzione: Autentica l’amministratore (unico utente), inizializza la sessione.
    Ritorno: true/false
  */
  public static function login($username, $password) {
    $pdo = DB::conn();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND active = 1 LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    $ok = $user && password_verify($password, $user['password_hash']);
    if (!$ok) {
      return false;
    }
    if ($user['role'] !== 'admin') { return false; }
    $_SESSION['user'] = ['id'=>$user['id'],'username'=>$user['username'],'role'=>$user['role'],'name'=>$user['username']];
    return true;
  }
  // Effettua logout e registra evento
  public static function logout() {
    unset($_SESSION['user']);
  }
  // Recupera IP del client
  private static function ip() { return $_SERVER['REMOTE_ADDR'] ?? null; }
  // Verifica se l’utente corrente è admin
  public static function isAdmin() { $u = self::user(); return $u && $u['role'] === 'admin'; }
}

