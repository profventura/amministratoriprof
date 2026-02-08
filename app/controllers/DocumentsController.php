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
  public function downloadByPath() {
    Auth::require();
    $rel = isset($_GET['path']) ? (string)$_GET['path'] : '';
    $rel = str_replace(['\\'], ['/' ], $rel);
    $rel = ltrim($rel, '/');
    if ($rel === '' || strpos($rel, '..') !== false) { http_response_code(404); echo 'Percorso non valido'; return; }
    if (!(str_starts_with($rel, 'app/templates/') || str_starts_with($rel, 'storage/documents/'))) { http_response_code(404); echo 'Percorso non consentito'; return; }
    $abs = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $rel;
    if (!is_file($abs)) { http_response_code(404); echo 'File non presente'; return; }
    $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
    $mime = 'application/octet-stream';
    if ($ext === 'pdf') $mime = 'application/pdf';
    elseif ($ext === 'html' || $ext === 'htm') $mime = 'text/html; charset=utf-8';
    elseif ($ext === 'docx') $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    header('Content-Type: '.$mime);
    header('Content-Length: '.filesize($abs));
    header('Content-Disposition: attachment; filename="'.basename($abs).'"');
    readfile($abs);
  }
}
