<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\Helpers;
use App\Models\Document;
class DocumentsController {
  public function download($id) {
    Auth::require();
    $doc = (new Document())->find((int)$id);
    if (!$doc) { http_response_code(404); echo 'Documento non trovato'; return; }
    $rel = str_replace(['\\', '..'], ['/', ''], $doc['file_path']);
    $abs = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $rel;
    if (!is_file($abs)) { http_response_code(404); echo 'File non presente'; return; }
    $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
    $mime = ($ext === 'pdf') ? 'application/pdf' : 'text/html; charset=utf-8';
    header('Content-Type: '.$mime);
    header('Content-Length: '.filesize($abs));
    header('Content-Disposition: inline; filename="'.basename($abs).'"');
    readfile($abs);
  }
}
