<?php
/*
  File: DB.php
  Scopo: Gestione della connessione globale al database MySQL tramite PDO.
  Spiegazione: Espone un metodo statico che crea (una sola volta) e restituisce
  un'istanza PDO configurata. Viene usato da modelli e componenti core.
*/
namespace App\Core;
use PDO;
class DB {
  private static $pdo;
  /*
    Metodo: conn
    Ritorna: istanza PDO condivisa in tutta l’applicazione.
    Dettagli: se non esiste ancora, la crea leggendo le credenziali da config.php
    e abilita la modalità di errore con eccezioni e il fetch associativo di default.
  */
  public static function conn() {
    if (!self::$pdo) {
      $config = require __DIR__ . '/../config.php';
      $host = $config['db']['host'];
      $port = $config['db']['port'];
      $name = $config['db']['name'];
      $charset = $config['db']['charset'];
      $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
      $user = $config['db']['user'];
      $pass = $config['db']['pass'];
      self::$pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);
    }
    return self::$pdo;
  }
}
