<?php
/*
  File: Model.php
  Scopo: Classe base per tutti i modelli, inizializza la connessione PDO.
  Spiegazione: I modelli specifici erediteranno questa classe per accedere al DB.
*/
namespace App\Models;
use App\Core\DB;
class Model {
  protected $pdo;
  // Inietta la connessione PDO condivisa
  public function __construct() { $this->pdo = DB::conn(); }
}

