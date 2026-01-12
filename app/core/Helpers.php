<?php
/*
  File: Helpers.php
  Scopo: Funzioni di utilità generiche (URL, redirect, rendering view, flash messages, upload file).
  Spiegazione: Centralizza operazioni comuni utilizzate in tutto il progetto.
*/
namespace App\Core;
class Helpers {
  // Costruisce un URL relativo alla base dello script
  public static function url($path = '') {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base = $scriptDir === '/' ? '' : $scriptDir;
    return $base . '/' . ltrim($path, '/');
  }
  // Esegue un redirect HTTP verso un path interno o URL assoluto
  public static function redirect($path) {
    if (!preg_match('#^https?://#', $path)) {
      $path = self::url($path);
    }
    header('Location: '.$path); exit;
  }
  // Renderizza una vista passando i dati e includendo il layout principale
  public static function view($template, $data = []) { extract($data); require __DIR__ . '/../views/layout.php'; }
  // Legge un parametro da POST/GET con default
  public static function param($key, $default = null) { return $_POST[$key] ?? $_GET[$key] ?? $default; }
  /*
    Metodo: fileUpload
    Funzione: Gestisce l’upload di un file spostandolo nella cartella desiderata.
    Ritorno: path pubblico utilizzabile per servire il file, oppure null se non presente.
  */
  public static function fileUpload($fileKey, $destDir) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) return null;
    $name = basename($_FILES[$fileKey]['name']);
    $safe = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','_', $name);
    if (!is_dir($destDir)) { mkdir($destDir, 0777, true); }
    $path = rtrim($destDir,'/\\') . DIRECTORY_SEPARATOR . $safe;
    move_uploaded_file($_FILES[$fileKey]['tmp_name'], $path);
    return 'public/uploads/'.$safe;
  }
  // Aggiunge un messaggio flash in sessione
  public static function addFlash($type, $message) {
    if (!isset($_SESSION['__flashes'])) { $_SESSION['__flashes'] = []; }
    $_SESSION['__flashes'][] = ['type'=>$type,'message'=>$message];
  }
  // Recupera e svuota i messaggi flash dalla sessione
  public static function getFlashes() {
    $f = $_SESSION['__flashes'] ?? [];
    unset($_SESSION['__flashes']);
    return $f;
  }
}
