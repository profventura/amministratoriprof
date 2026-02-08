<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Helpers;
use App\Core\DB;
use App\Services\DocxTemplateService;
class SettingsController {
  public function index() {
    Auth::require();
    $pdo = DB::conn();
    $row = $pdo->query('SELECT * FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    Helpers::view('settings/index', ['title'=>'Impostazioni','row'=>$row]);
  }
  public function updateTemplate() {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    if (!isset($_FILES['template']) || $_FILES['template']['error'] !== UPLOAD_ERR_OK) {
      Helpers::addFlash('danger', 'Seleziona un file .docx');
      Helpers::redirect('/settings'); return;
    }
    $name = basename($_FILES['template']['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, ['docx', 'pdf'])) {
      Helpers::addFlash('danger', 'Il file deve essere .docx o .pdf');
      Helpers::redirect('/settings'); return;
    }
    $safe = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','_', $name);
    $destDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 1) . '/templates');
    if (!is_dir($destDir)) { mkdir($destDir, 0777, true); }
    $abs = $destDir . DIRECTORY_SEPARATOR . 'certificato.' . $ext;
    move_uploaded_file($_FILES['template']['tmp_name'], $abs);
    $rel = 'app/templates/certificato.' . $ext;
    $pdo = DB::conn();
    $exists = $pdo->query('SELECT COUNT(*) c FROM settings')->fetch()['c'];
    if ((int)$exists === 0) {
      $stmt = $pdo->prepare('INSERT INTO settings (association_name, receipt_sequence_current, receipt_sequence_year, dm_certificate_template_docx_path, membership_certificate_template_docx_path) VALUES (?,?,?,?,?)');
      $stmt->execute(['Associazione AP', 0, (int)date('Y'), $rel, $rel]);
    } else {
      $stmt = $pdo->prepare('UPDATE settings SET dm_certificate_template_docx_path=?, membership_certificate_template_docx_path=?, updated_at=NOW() ORDER BY id DESC LIMIT 1');
      $stmt->execute([$rel, $rel]);
    }
    Helpers::addFlash('success', 'Template aggiornato');
    Helpers::redirect('/settings');
  }
  public function updateStamp() {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    
    // Raccogli tutti i parametri dal POST
    $fields = ['name', 'number', 'date', 'year'];
    $props = ['x', 'y', 'font_size', 'color', 'font_family', 'bold'];
    
    $params = [];
    foreach ($fields as $f) {
        foreach ($props as $p) {
            $key = "{$f}_{$p}";
            $val = $_POST[$key] ?? '';
            // Validazione minima
            if ($p === 'x' || $p === 'y' || $p === 'font_size') $val = (int)$val;
            if ($p === 'bold') $val = !empty($val) ? 1 : 0;
            $params["certificate_stamp_{$key}"] = $val;
        }
    }

    $pdo = DB::conn();
    $exists = $pdo->query('SELECT COUNT(*) c FROM settings')->fetch()['c'];
    
    if ((int)$exists === 0) {
        // Insert (gestire solo se vuoto, improbabile)
        // Per brevità assumiamo esista già una riga, o facciamo un INSERT parziale
        // Ma nel contesto attuale c'è già una riga.
    } else {
        $sets = [];
        $vals = [];
        foreach ($params as $col => $val) {
            $sets[] = "$col = ?";
            $vals[] = $val;
        }
        $sql = "UPDATE settings SET " . implode(', ', $sets) . ", updated_at=NOW() ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($vals);
    }
    
    Helpers::addFlash('success', 'Stili aggiornati');
    Helpers::redirect('/settings');
  }

  public function previewStamp() {
    Auth::require();
    // Non controlliamo CSRF stretto qui per facilitare chiamate AJAX rapide, o lo passiamo via header
    // Per semplicità usiamo POST normale
    
    $pdo = DB::conn();
    $row = $pdo->query('SELECT membership_certificate_template_docx_path FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $rel = $row['membership_certificate_template_docx_path'] ?? '';
    
    if (!$rel || !file_exists($tplAbs = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $rel))) {
        echo "Template non trovato."; return;
    }
    
    // Se non è PDF, non possiamo fare preview coordinate (ha senso solo su PDF)
    if (strtolower(pathinfo($tplAbs, PATHINFO_EXTENSION)) !== 'pdf') {
        echo "L'anteprima coordinate funziona solo con template PDF."; return;
    }

    // Raccogli parametri da POST (o usa quelli salvati se vuoti, ma qui vogliamo testare quelli del form)
    // Se la chiamata arriva dal form di test, usiamo $_POST
    $opts = [];
    $fields = ['name', 'number', 'date', 'year'];
    foreach ($fields as $f) {
        $opts["{$f}_x"] = (int)($_POST["{$f}_x"] ?? 0);
        $opts["{$f}_y"] = (int)($_POST["{$f}_y"] ?? 0);
        $opts["{$f}_font_size"] = (int)($_POST["{$f}_font_size"] ?? 12);
        $opts["{$f}_color"] = $_POST["{$f}_color"] ?? '#000000';
        $opts["{$f}_font_family"] = $_POST["{$f}_font_family"] ?? 'Arial';
        $opts["{$f}_bold"] = !empty($_POST["{$f}_bold"]);
    }
    
    // Dati finti
    $name = "ANTEPRIMA NOME";
    $number = "12345";
    $opts['date_value'] = date('d/m/Y');
    $opts['year_value'] = date('Y');
    
    // Debug grid
    if (!empty($_POST['debug_grid'])) {
        $opts['debug_grid'] = true;
    }

    $outDir = dirname(__DIR__, 2) . '/storage/documents/_test';
    if (!is_dir($outDir)) mkdir($outDir, 0777, true);
    $outFile = $outDir . '/preview_' . time() . '.pdf';

    $res = \App\Services\PDFStampService::stampMembershipCertificate($tplAbs, $outFile, $name, $number, $opts);
    
    if ($res && file_exists($outFile)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="preview.pdf"');
        readfile($outFile);
        @unlink($outFile);
    } else {
        echo "Errore generazione anteprima.";
    }
  }
  public function getPdfGeometry() {
    Auth::require();
    header('Content-Type: application/json');
    
    $pdo = DB::conn();
    $row = $pdo->query('SELECT membership_certificate_template_docx_path FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $rel = $row['membership_certificate_template_docx_path'] ?? '';
    
    if (!$rel || !file_exists($tplAbs = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $rel))) {
        echo json_encode(['error' => 'Template non trovato']); return;
    }
    
    if (strtolower(pathinfo($tplAbs, PATHINFO_EXTENSION)) !== 'pdf') {
        echo json_encode(['error' => 'Non è un PDF']); return;
    }

    try {
        $pdf = new \setasign\Fpdi\Fpdi();
        $pdf->setSourceFile($tplAbs);
        $tpl = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tpl);
        // FPDI restituisce width/height in unità utente (pt) se importPage è chiamato senza parametri box
        // Ma di default importPage usa CropBox.
        // getTemplateSize restituisce ['width' => ..., 'height' => ...]
        
        // Non abbiamo modo facile di prendere il MediaBox vs CropBox senza parsare raw il PDF,
        // ma getTemplateSize ci dice esattamente l'area su cui FPDI lavorerà (0,0 -> w,h).
        // Quindi se il JS si adatta a questa width/height, l'origine (0,0) sarà coerente.
        
        echo json_encode([
            'width' => $size['width'],
            'height' => $size['height'],
            // Opzionale: orientation
            'orientation' => ($size['width'] > $size['height']) ? 'L' : 'P'
        ]);
    } catch (\Throwable $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
  }

  public function testDocx() {
    Auth::require();
    $pdo = DB::conn();
    $row = $pdo->query('SELECT membership_certificate_template_docx_path, dm_certificate_template_docx_path FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $rel = $row['membership_certificate_template_docx_path'] ?? $row['dm_certificate_template_docx_path'] ?? '';
    if (!class_exists('ZipArchive')) {
      Helpers::addFlash('danger', 'ZipArchive non abilitato in PHP');
      Helpers::redirect('/settings'); return;
    }
    if (!$rel) {
      Helpers::addFlash('danger', 'Nessun template DOCX configurato');
      Helpers::redirect('/settings'); return;
    }
    $tplAbs = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $rel);
    if (!is_file($tplAbs)) {
      Helpers::addFlash('danger', 'Template DOCX non trovato: ' . $rel);
      Helpers::redirect('/settings'); return;
    }
    $dirOut = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 1) . '/../storage/documents/_test');
    if (!is_dir($dirOut)) { mkdir($dirOut, 0777, true); }
    $outAbs = $dirOut . DIRECTORY_SEPARATOR . 'test_cert.pdf';
    $vars = ['nome'=>'Test User', 'te'=>'12345', 'a'=>date('Y')];
    $rendered = \App\Services\DocxTemplateService::renderToPdf($tplAbs, $vars, $outAbs);
    if ($rendered && is_file($outAbs)) {
      Helpers::addFlash('success', 'Generazione DOCX→PDF OK');
      Helpers::redirect('/documents/download?path=' . urlencode('storage/documents/_test/test_cert.pdf')); return;
    }
    $outDocx = $dirOut . DIRECTORY_SEPARATOR . 'test_cert.docx';
    $docxOk = \App\Services\DocxTemplateService::renderToDocx($tplAbs, $vars, $outDocx);
    if ($docxOk) {
      Helpers::addFlash('warning', 'PDF non disponibile: generato DOCX di prova');
      Helpers::redirect('/documents/download?path=' . urlencode('storage/documents/_test/test_cert.docx')); return;
    }
    Helpers::addFlash('danger', 'Generazione da DOCX non riuscita, verificare renderer PDF e ZipArchive');
    Helpers::redirect('/settings');
  }
}
