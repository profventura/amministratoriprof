<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Helpers;
use App\Core\DB;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Document;
use App\Services\DocumentService;
use App\Services\DocxTemplateService;
class CertificatesController {
  public function generateMember() {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    $memberId = (int)($_POST['member_id'] ?? 0);
    $year = (int)($_POST['year'] ?? date('Y'));
    if ($memberId <= 0) { Helpers::addFlash('danger', 'Seleziona un socio'); Helpers::redirect('/members'); return; }
    $pdo = DB::conn();
    $assoc = $pdo->query('SELECT association_name, membership_certificate_template_docx_path FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $mbr = (new Member())->find($memberId);
        $membership = (new Membership())->getOrCreate($memberId, $year);
    
    // Recupera la data di iscrizione (payment_date) dalla membership, se esiste
    $paymentDate = !empty($membership['payment_date']) ? date('d/m/Y', strtotime($membership['payment_date'])) : date('d/m/Y');
    
    $vars = [
      'association_name' => $assoc ? $assoc['association_name'] : 'Associazione AP',
      'member_name' => $mbr ? ($mbr['first_name'].' '.$mbr['last_name']) : '',
      'member_email' => $mbr ? ($mbr['email'] ?? '') : '',
      'year' => (string)$year,
      'date' => $paymentDate,
    ];
    $basename = 'certificate_' . preg_replace('/[^a-z0-9]+/i','_', $vars['member_name']) . '_' . date('dmYHis');
    $outputAbsBase = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 1) . '/../storage/documents/membership_certificate/'.$year.'/'.$basename);
    $docPathPublic = null;
    $tplRel = $assoc['membership_certificate_template_docx_path'] ?? null;
    $tplAbs = $tplRel ? str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $tplRel) : null;
    if ($tplAbs && is_file($tplAbs)) {
      $ext = strtolower(pathinfo($tplAbs, PATHINFO_EXTENSION));
      if ($ext === 'pdf') {
        // Option B: Direct PDF stamping (perfect layout)
        $finalPdf = $outputAbsBase . '.pdf';
        
        // Recuperiamo TUTTI i parametri dal DB
        $s = $pdo->query('SELECT * FROM settings ORDER BY id DESC LIMIT 1')->fetch();
        
        // Costruiamo array opzioni completo (simile a SettingsController::previewStamp)
        $opts = [];
        $fields = ['name', 'number', 'date', 'year'];
        foreach ($fields as $f) {
            $opts["{$f}_x"] = (int)($s["certificate_stamp_{$f}_x"] ?? 0);
            $opts["{$f}_y"] = (int)($s["certificate_stamp_{$f}_y"] ?? 0);
            $opts["{$f}_font_size"] = (int)($s["certificate_stamp_{$f}_font_size"] ?? 12);
            $opts["{$f}_color"] = $s["certificate_stamp_{$f}_color"] ?? '#000000';
            $opts["{$f}_font_family"] = $s["certificate_stamp_{$f}_font_family"] ?? 'Arial';
            $opts["{$f}_bold"] = !empty($s["certificate_stamp_{$f}_bold"]);
        }
        
        // Assegniamo i valori espliciti per data e anno
        $opts['date_value'] = $vars['date'];
        $opts['year_value'] = $vars['year'];
        
        // Passiamo tutto al service
        \App\Services\PDFStampService::stampMembershipCertificate(
            $tplAbs, 
            $finalPdf, 
            $vars['member_name'], 
            $membership['id'], 
            $opts
        );
        
        if (is_file($finalPdf)) { 
          $docPathPublic = 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.pdf'; 
        }
      } else {
        // Option A: DOCX placeholder replacement
          $outPdfBase = $outputAbsBase . '_base.pdf';
          $vars2 = [
             'nome' => $vars['member_name'],
             'te' => strval($membership['id']),
             'a' => (string)$year,
             'data' => date('d/m/Y'),
           ];
          $compiledPdf = \App\Services\DocxTemplateService::renderToPdf($tplAbs, $vars2, $outPdfBase);
          if ($compiledPdf && is_file($outPdfBase)) {
            // Se PDF generato con successo, lo usiamo
            $docPathPublic = 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.pdf';
            @rename($outPdfBase, $outputAbsBase . '.pdf');
          } else {
            // FALLBACK: Se il PDF fallisce, restituiamo il DOCX compilato
            $outDocx = $outputAbsBase . '.docx';
            $docxOk = \App\Services\DocxTemplateService::renderToDocx($tplAbs, $vars2, $outDocx);
            if ($docxOk) {
              $docPathPublic = 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.docx';
            } else {
              // Fallback estremo HTML
              $tpl = dirname(__DIR__) . '/templates/documents/membership_certificate.html';
              $html = DocumentService::renderTemplate($tpl, $vars);
              $paths = DocumentService::saveDocument('membership_certificate', $year, $basename, $html);
              $docPathPublic = $paths['pdf']
                ? 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.pdf'
                : 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.html';
            }
          }
      }
    } else {
      $tpl = dirname(__DIR__) . '/templates/documents/membership_certificate.html';
      $html = DocumentService::renderTemplate($tpl, $vars);
      $paths = DocumentService::saveDocument('membership_certificate', $year, $basename, $html);
      $docPathPublic = $paths['pdf']
        ? 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.pdf'
        : 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.html';
    }
    (new Document())->create($memberId, 'membership_certificate', $year, $docPathPublic);
    Helpers::addFlash('success', 'Certificato generato');
    Helpers::redirect('/members/'.$memberId);
  }
  public function generateMassByIds($year, $ids) {
    Auth::require();
    if (!Auth::isAdmin()) { http_response_code(403); echo '403'; return; }
    
    $pdo = DB::conn();
    // Recupera solo i membri richiesti che hanno una membership valida per l'anno (opzionale: o crea al volo?)
    // In questo contesto (MembershipsController), le membership esistono.
    
    // Prepariamo dati comuni
    $assoc = $pdo->query('SELECT association_name, membership_certificate_template_docx_path FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $tplRel = $assoc['membership_certificate_template_docx_path'] ?? null;
    $tplAbs = $tplRel ? str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $tplRel) : null;
    $tplHtml = dirname(__DIR__) . '/templates/documents/membership_certificate.html';
    
    // Parametri timbro PDF
    $s = $pdo->query('SELECT * FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $opts = [];
    $fields = ['name', 'number', 'date', 'year'];
    foreach ($fields as $f) {
        $opts["{$f}_x"] = (int)($s["certificate_stamp_{$f}_x"] ?? 0);
        $opts["{$f}_y"] = (int)($s["certificate_stamp_{$f}_y"] ?? 0);
        $opts["{$f}_font_size"] = (int)($s["certificate_stamp_{$f}_font_size"] ?? 12);
        $opts["{$f}_color"] = $s["certificate_stamp_{$f}_color"] ?? '#000000';
        $opts["{$f}_font_family"] = $s["certificate_stamp_{$f}_font_family"] ?? 'Arial';
        $opts["{$f}_bold"] = !empty($s["certificate_stamp_{$f}_bold"]);
    }

    $count = 0;
    $m = new Membership();
    $mbrModel = new Member();

    foreach ($ids as $membershipId) {
        // Attenzione: qui $ids sono ID di 'memberships', non di 'members'!
        // Dobbiamo recuperare il member_id dalla membership
        $membership = $m->find((int)$membershipId);
        if (!$membership) continue;
        
        $memberId = $membership['member_id'];
        $mbr = $mbrModel->find($memberId);
        if (!$mbr) continue;

        // Recupera data pagamento
        $paymentDate = !empty($membership['payment_date']) ? date('d/m/Y', strtotime($membership['payment_date'])) : date('d/m/Y');

        $vars = [
            'association_name' => $assoc ? $assoc['association_name'] : 'Associazione AP',
            'member_name' => $mbr['last_name'].' '.$mbr['first_name'],
            'member_email' => $mbr['email'] ?? '',
            'year' => (string)$year,
            'date' => $paymentDate,
        ];
        
        $basename = 'certificate_' . preg_replace('/[^a-z0-9]+/i','_', $vars['member_name']) . '_' . date('dmYHis');
        $outputAbsBase = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 1) . '/../storage/documents/membership_certificate/'.$year.'/'.$basename);
        $docPathPublic = null;

        if ($tplAbs && is_file($tplAbs)) {
            $ext = strtolower(pathinfo($tplAbs, PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                // PDF Stamp
                $finalPdf = $outputAbsBase . '.pdf';
                
                // Assegna valori data e anno
                $opts['date_value'] = $vars['date'];
                $opts['year_value'] = $vars['year'];
                
                \App\Services\PDFStampService::stampMembershipCertificate($tplAbs, $finalPdf, $vars['member_name'], $membership['id'], $opts);
                if (is_file($finalPdf)) { 
                    $docPathPublic = 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.pdf'; 
                }
            } else {
                // DOCX
                $outPdfBase = $outputAbsBase . '_base.pdf';
                $vars2 = [
                    'nome' => $vars['member_name'],
                    'te' => strval($membership['id']),
                    'a' => (string)$year,
                ];
                $compiledPdf = \App\Services\DocxTemplateService::renderToPdf($tplAbs, $vars2, $outPdfBase);
                if ($compiledPdf && is_file($outPdfBase)) {
                    $docPathPublic = 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.pdf';
                    @rename($outPdfBase, $outputAbsBase . '.pdf');
                } else {
                     // Fallback DOCX
                    $outDocx = $outputAbsBase . '.docx';
                    if (\App\Services\DocxTemplateService::renderToDocx($tplAbs, $vars2, $outDocx)) {
                        $docPathPublic = 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.docx';
                    }
                }
            }
        } else {
            // HTML Fallback
            $html = DocumentService::renderTemplate($tplHtml, $vars);
            $paths = DocumentService::saveDocument('membership_certificate', $year, $basename, $html);
            $docPathPublic = $paths['pdf'] 
                ? 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.pdf'
                : 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.html';
        }
        
        if ($docPathPublic) {
            // Salva documento associato al membro
            (new Document())->create((int)$memberId, 'membership_certificate', $year, $docPathPublic);
            $count++;
        }
    }
    
    Helpers::addFlash('success', 'Generati ' . $count . ' certificati');
    Helpers::redirect('/memberships?year='.$year);
  }

  public function generateSelected() {
    Auth::require();
    if (!Auth::isAdmin()) { http_response_code(403); echo '403'; return; }
    // Non controlliamo CSRF qui se chiamato internamente, ma se è una rotta pubblica sì.
    // Se chiamato da MembersController::bulkAction, i dati sono in $_POST o passati come arg.
    // Assumiamo che questo metodo sia chiamato via rotta POST dedicata o invocato da MembersController.
    // Se invocato direttamente:
    
    $ids = $_POST['selected_ids'] ?? [];
    if (empty($ids)) {
        Helpers::addFlash('warning', 'Nessun socio selezionato');
        Helpers::redirect('/members');
        return;
    }
    
    $year = (int)date('Y'); // Usa anno corrente per default
    $pdo = DB::conn();
    $assoc = $pdo->query('SELECT association_name, membership_certificate_template_docx_path FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $tplHtml = dirname(__DIR__) . '/templates/documents/membership_certificate.html';
    $tplRel = $assoc['membership_certificate_template_docx_path'] ?? null;
    $tplAbs = $tplRel ? str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $tplRel) : null;
    
    // Parametri timbro PDF
    $s = $pdo->query('SELECT * FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $opts = [];
    $fields = ['name', 'number', 'date', 'year'];
    foreach ($fields as $f) {
        $opts["{$f}_x"] = (int)($s["certificate_stamp_{$f}_x"] ?? 0);
        $opts["{$f}_y"] = (int)($s["certificate_stamp_{$f}_y"] ?? 0);
        $opts["{$f}_font_size"] = (int)($s["certificate_stamp_{$f}_font_size"] ?? 12);
        $opts["{$f}_color"] = $s["certificate_stamp_{$f}_color"] ?? '#000000';
        $opts["{$f}_font_family"] = $s["certificate_stamp_{$f}_font_family"] ?? 'Arial';
        $opts["{$f}_bold"] = !empty($s["certificate_stamp_{$f}_bold"]);
    }

    $count = 0;
    foreach ($ids as $id) {
        $mbr = (new Member())->find((int)$id);
        if (!$mbr) continue;
        
        // Assicura iscrizione per l'anno corrente
        $membership = (new Membership())->getOrCreate((int)$id, $year);
        
        $vars = [
            'association_name' => $assoc ? $assoc['association_name'] : 'Associazione AP',
            'member_name' => $mbr['last_name'].' '.$mbr['first_name'],
            'member_email' => $mbr['email'] ?? '',
            'year' => (string)$year,
            'date' => date('Y-m-d'),
        ];
        
        $basename = 'certificate_' . preg_replace('/[^a-z0-9]+/i','_', $vars['member_name']) . '_' . date('dmYHis');
        $outputAbsBase = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 1) . '/../storage/documents/membership_certificate/'.$year.'/'.$basename);
        $docPathPublic = null;

        if ($tplAbs && is_file($tplAbs)) {
            $ext = strtolower(pathinfo($tplAbs, PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                // PDF Stamp
                $finalPdf = $outputAbsBase . '.pdf';
                \App\Services\PDFStampService::stampMembershipCertificate($tplAbs, $finalPdf, $vars['member_name'], $membership['id'], $opts);
                if (is_file($finalPdf)) { 
                    $docPathPublic = 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.pdf'; 
                }
            } else {
                // DOCX
                $outPdfBase = $outputAbsBase . '_base.pdf';
                $vars2 = [
                    'nome' => $vars['member_name'],
                    'te' => strval($membership['id']),
                    'a' => (string)$year,
                ];
                $compiledPdf = \App\Services\DocxTemplateService::renderToPdf($tplAbs, $vars2, $outPdfBase);
                if ($compiledPdf && is_file($outPdfBase)) {
                    $docPathPublic = 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.pdf';
                    @rename($outPdfBase, $outputAbsBase . '.pdf');
                } else {
                    // Fallback DOCX
                    $outDocx = $outputAbsBase . '.docx';
                    if (\App\Services\DocxTemplateService::renderToDocx($tplAbs, $vars2, $outDocx)) {
                        $docPathPublic = 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.docx';
                    }
                }
            }
        } else {
            // HTML Fallback
            $html = DocumentService::renderTemplate($tplHtml, $vars);
            $paths = DocumentService::saveDocument('membership_certificate', $year, $basename, $html);
            $docPathPublic = $paths['pdf'] 
                ? 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.pdf'
                : 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.html';
        }
        
        if ($docPathPublic) {
            (new Document())->create((int)$id, 'membership_certificate', $year, $docPathPublic);
            $count++;
        }
    }
    
    Helpers::addFlash('success', 'Generati ' . $count . ' certificati');
    Helpers::redirect('/members');
  }
}
