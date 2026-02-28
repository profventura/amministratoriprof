<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\Helpers;
use App\Models\Member; // Assumendo che Member sia usato per i check
use App\Core\DB;

class DocumentsController {

  // Metodo originale download per ID
  public function download($id) {
    Auth::require();
    // ... logica esistente se c'era ...
    // Ma nel codice letto sopra c'era un mix strano.
    // Ripristiniamo una versione pulita.
    
    $pdo = DB::conn();
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
    $stmt->execute([(int)$id]);
    $doc = $stmt->fetch();
    
    if (!$doc) { http_response_code(404); echo 'Documento non trovato'; return; }
    
    $rel = $doc['file_path'];
    // Pulizia path
    $rel = str_replace(['\\', '..'], ['/', ''], $rel);
    // Path assoluto
    $abs = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $rel;
    
    if (!is_file($abs)) { http_response_code(404); echo 'File fisico non trovato: ' . htmlspecialchars($rel); return; }
    
    $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
    $mime = ($ext === 'pdf') ? 'application/pdf' : 'text/html; charset=utf-8';
    
    header('Content-Type: '.$mime);
    header('Content-Length: '.filesize($abs));
    header('Content-Disposition: inline; filename="'.basename($abs).'"');
    readfile($abs);
  }

  // Metodo per scaricare file dato un path relativo (es. template)
  public function downloadByPath() {
    Auth::require();
    $rel = isset($_GET['path']) ? (string)$_GET['path'] : '';
    
    // Normalizza separatori
    $rel = str_replace('\\', '/', $rel);
    // Rimuovi slash iniziali e '..' per sicurezza
    $rel = ltrim($rel, '/');
    if ($rel === '' || strpos($rel, '..') !== false) { 
        http_response_code(404); echo 'Percorso non valido'; return; 
    }
    
    // Whitelist cartelle consentite
    // app/templates/ per i template docx/pdf
    // storage/documents/ per i file generati
    // Aggiungiamo flessibilità se necessario
    if (!str_starts_with($rel, 'app/templates/') && !str_starts_with($rel, 'storage/documents/')) {
        http_response_code(403); echo 'Accesso al percorso non consentito'; return; 
    }
    
    $abs = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $rel;
    
    if (!is_file($abs)) { 
        http_response_code(404); echo 'File non presente su disco'; return; 
    }
    
    $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
    $mime = 'application/octet-stream';
    if ($ext === 'pdf') $mime = 'application/pdf';
    elseif ($ext === 'html' || $ext === 'htm') $mime = 'text/html; charset=utf-8';
    elseif ($ext === 'docx') $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    
    // Importante: disabilitare compressione output se attiva per evitare corruzione binari
    if (ini_get('zlib.output_compression')) {
        ini_set('zlib.output_compression', 'Off');
    }
    
    header('Content-Type: '.$mime);
    header('Content-Length: '.filesize($abs));
    // Usa 'inline' per visualizzazione nel browser (es. PDF.js), 'attachment' per download forzato
    // Per l'editor visuale serve inline o comunque accessibile via XHR
    header('Content-Disposition: inline; filename="'.basename($abs).'"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    readfile($abs);
  }

  public function delete($id) {
    Auth::require();
    if (!Auth::isAdmin()) { http_response_code(403); echo '403 Forbidden'; return; }
    
    // CSRF check
    if (!\App\Core\CSRF::validate($_POST['csrf'] ?? '')) { 
        Helpers::addFlash('danger', 'Token CSRF non valido'); 
        Helpers::redirect($_SERVER['HTTP_REFERER']); 
        return; 
    }
    
    $pdo = DB::conn();
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
    $stmt->execute([(int)$id]);
    $doc = $stmt->fetch();
    
    if (!$doc) { 
        Helpers::addFlash('danger', 'Documento non trovato'); 
        Helpers::redirect($_SERVER['HTTP_REFERER']); 
        return; 
    }
    
    // Elimina file fisico
    $rel = $doc['file_path'];
    $rel = str_replace(['\\', '..'], ['/', ''], $rel);
    $abs = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $rel;
    
    if (is_file($abs)) {
        @unlink($abs);
    }
    
    // Elimina record DB
    $pdo->prepare("DELETE FROM documents WHERE id = ?")->execute([(int)$id]);
    
    Helpers::addFlash('success', 'Documento eliminato');
    Helpers::redirect($_SERVER['HTTP_REFERER']);
  }

  public function sendEmail($id) {
      Auth::require();
      if (!\App\Core\CSRF::validate($_POST['csrf'] ?? '')) { 
          Helpers::addFlash('danger', 'Token CSRF non valido'); 
          Helpers::redirect($_SERVER['HTTP_REFERER']); 
          return; 
      }

      $pdo = DB::conn();
      $stmt = $pdo->prepare("SELECT d.*, m.first_name, m.last_name, m.email FROM documents d JOIN members m ON m.id = d.member_id WHERE d.id = ?");
      $stmt->execute([(int)$id]);
      $doc = $stmt->fetch();

      if (!$doc) {
          Helpers::addFlash('danger', 'Documento non trovato');
          Helpers::redirect($_SERVER['HTTP_REFERER']);
          return;
      }

      $memberEmail = $doc['email'];
      if (!filter_var($memberEmail, FILTER_VALIDATE_EMAIL)) {
          Helpers::addFlash('danger', 'Indirizzo email del socio non valido');
          Helpers::redirect($_SERVER['HTTP_REFERER']);
          return;
      }

      $filePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $doc['file_path'];
      if (!file_exists($filePath)) {
          Helpers::addFlash('danger', 'File fisico non trovato');
          Helpers::redirect($_SERVER['HTTP_REFERER']);
          return;
      }

      // Recupera impostazioni email per oggetto/corpo
      $settings = $pdo->query("SELECT * FROM email_settings ORDER BY id DESC LIMIT 1")->fetch();
      
      $subject = "Nuovo Documento";
      $body = "In allegato trovi il documento richiesto.";

      // Personalizzazione base in base al tipo documento
      if ($doc['type'] === 'course_certificate' || $doc['type'] === 'dm_certificate') {
          $subject = $settings['email_dm_certificate_subject'] ?: 'Attestato di Partecipazione';
          $body = $settings['email_dm_certificate_body'] ?: 'Gentile socio, in allegato trovi il tuo attestato.';
      } elseif ($doc['type'] === 'membership_certificate') {
          $subject = $settings['email_certificate_subject'] ?: 'Certificato di Iscrizione';
          $body = $settings['email_certificate_body'] ?: 'Gentile socio, in allegato trovi il tuo certificato di iscrizione.';
      }

      // Sostituzione placeholder (base)
      $memberName = $doc['first_name'] . ' ' . $doc['last_name'];
      $body = str_replace(['{{NOME}}', '{{ANNO}}'], [$memberName, $doc['year']], $body);

      // Invio
      // Assicurati che EmailService sia incluso o autoloadato
      // require_once __DIR__ . '/../services/EmailService.php'; // Se non c'è autoload
      
      $result = \App\Services\EmailService::send(
          $memberEmail, 
          $memberName, 
          $subject, 
          $body, 
          [['path' => $filePath, 'name' => basename($filePath)]]
      );

      if ($result['success']) {
          Helpers::addFlash('success', 'Email inviata con successo a ' . $memberEmail);
      } else {
          Helpers::addFlash('danger', 'Errore invio email: ' . $result['error']);
      }

      Helpers::redirect($_SERVER['HTTP_REFERER']);
  }
}
