<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Helpers;
use App\Core\DB;
use App\Models\Course;
use App\Models\CourseParticipant;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Document;
use App\Services\DocumentService;
use App\Services\DocxTemplateService;
use App\Services\PDFStampService;

class DMCertificatesController {
  public function generateSingle($courseId) {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    $memberId = (int)($_POST['member_id'] ?? 0);
    $course = (new Course())->find((int)$courseId);
    if (!$course || $memberId <= 0) { Helpers::addFlash('danger','Dati non validi'); Helpers::redirect('/courses/'.$courseId); return; }
    $pdo = DB::conn();
    $assoc = $pdo->query('SELECT * FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $mbr = (new Member())->find($memberId);
    $vars = [
      'association_name' => $assoc ? $assoc['association_name'] : 'Associazione AP',
      'member_name' => $mbr ? ($mbr['first_name'].' '.$mbr['last_name']) : '',
      'member_email' => $mbr ? ($mbr['email'] ?? '') : '',
      'course_title' => str_replace("\r\n", "\n", $course['title']), // Normalizza newline per PDFStampService
      'course_date' => $course['course_date'],
      'start_time' => $course['start_time'] ?? '',
      'end_time' => $course['end_time'] ?? '',
      'year' => (string)$course['year'],
      'date' => date('Y-m-d'),
    ];
    $basename = 'dm_' . preg_replace('/[^a-z0-9]+/i','_', $vars['member_name']) . '_' . (int)$courseId . '_' . date('dmYHis');
    $outputAbsBase = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 1) . '/../storage/documents/dm_certificate/'.$course['year'].'/'.$basename);
    $docPathPublic = null;
    $tplRel = $assoc['dm_certificate_template_docx_path'] ?? null;
    $tplAbs = $tplRel ? str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $tplRel) : null;
    
    if ($tplAbs && is_file($tplAbs)) {
      $ext = strtolower(pathinfo($tplAbs, PATHINFO_EXTENSION));
      if ($ext === 'pdf') {
        // Option B: Direct PDF stamping (manual calibration)
        $outPdf = $outputAbsBase . '.pdf';
        $mem = (new Membership())->getOrCreate($memberId, (int)$course['year']);
        
        $opts = [
            'name_x' => (int)($assoc['dm_certificate_stamp_name_x'] ?? 100),
            'name_y' => (int)($assoc['dm_certificate_stamp_name_y'] ?? 120),
            'name_font_size' => (int)($assoc['dm_certificate_stamp_name_font_size'] ?? 16),
            'name_color' => $assoc['dm_certificate_stamp_name_color'] ?? '#000000',
            'name_font_family' => $assoc['dm_certificate_stamp_name_font_family'] ?? 'Arial',
            'name_bold' => $assoc['dm_certificate_stamp_name_bold'] ?? 1,
            
            'course_title_x' => (int)($assoc['dm_certificate_stamp_course_title_x'] ?? 100),
            'course_title_y' => (int)($assoc['dm_certificate_stamp_course_title_y'] ?? 140),
            'course_title_font_size' => (int)($assoc['dm_certificate_stamp_course_title_font_size'] ?? 16),
            'course_title_color' => $assoc['dm_certificate_stamp_course_title_color'] ?? '#000000',
            'course_title_font_family' => $assoc['dm_certificate_stamp_course_title_font_family'] ?? 'Arial',
            'course_title_bold' => $assoc['dm_certificate_stamp_course_title_bold'] ?? 1,
            'course_title_value' => $vars['course_title'], // Usa il valore normalizzato da $vars

            'date_x' => (int)($assoc['dm_certificate_stamp_date_x'] ?? 0),
            'date_y' => (int)($assoc['dm_certificate_stamp_date_y'] ?? 0),
            'date_font_size' => (int)($assoc['dm_certificate_stamp_date_font_size'] ?? 12),
            'date_color' => $assoc['dm_certificate_stamp_date_color'] ?? '#000000',
            'date_font_family' => $assoc['dm_certificate_stamp_date_font_family'] ?? 'Arial',
            'date_bold' => $assoc['dm_certificate_stamp_date_bold'] ?? 0,
            'date_value' => date('d/m/Y'),

            'year_x' => (int)($assoc['dm_certificate_stamp_year_x'] ?? 0),
            'year_y' => (int)($assoc['dm_certificate_stamp_year_y'] ?? 0),
            'year_font_size' => (int)($assoc['dm_certificate_stamp_year_font_size'] ?? 12),
            'year_color' => $assoc['dm_certificate_stamp_year_color'] ?? '#000000',
            'year_font_family' => $assoc['dm_certificate_stamp_year_font_family'] ?? 'Arial',
            'year_bold' => $assoc['dm_certificate_stamp_year_bold'] ?? 0,
            'year_value' => (string)$course['year'],
        ];

        PDFStampService::stampMembershipCertificate($tplAbs, $outPdf, $vars['member_name'], $mem['id'], $opts);
        if (is_file($outPdf)) {
           $docPathPublic = 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.pdf';
        }
      } else {
        // Option A: DOCX/HTML
        $outPdf = $outputAbsBase . '.pdf';
        $mem = (new Membership())->getOrCreate($memberId, (int)$course['year']);
        $vars2 = [
          'nome' => $vars['member_name'],
            'te' => strval($mem['id']),
            'a' => (string)$course['year'],
            'data' => date('d/m/Y'),
          ];
        $rendered = DocxTemplateService::renderToPdf($tplAbs, $vars2, $outPdf);
        if ($rendered) {
          $docPathPublic = 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.pdf';
        } else {
          $outDocx = $outputAbsBase . '.docx';
          $docxOk = DocxTemplateService::renderToDocx($tplAbs, $vars2, $outDocx);
          if ($docxOk) {
            $docPathPublic = 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.docx';
          } else {
          $tpl = dirname(__DIR__) . '/templates/documents/dm_certificate.html';
          $html = DocumentService::renderTemplate($tpl, $vars);
          $paths = DocumentService::saveDocument('dm_certificate', (int)$course['year'], $basename, $html);
          $docPathPublic = $paths['pdf']
            ? 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.pdf'
            : 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.html';
          }
        }
      }
    } else {
      $tpl = dirname(__DIR__) . '/templates/documents/dm_certificate.html';
      $html = DocumentService::renderTemplate($tpl, $vars);
      $paths = DocumentService::saveDocument('dm_certificate', (int)$course['year'], $basename, $html);
      $docPathPublic = $paths['pdf']
        ? 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.pdf'
        : 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.html';
    }
    if ($docPathPublic) {
        // Rimuovi vecchio certificato se esiste
        $cpModel = new CourseParticipant();
        $oldDocId = $cpModel->getCertificateId((int)$courseId, $memberId);
        if ($oldDocId) {
            $docModel = new Document();
            $oldDoc = $docModel->find($oldDocId);
            if ($oldDoc) {
                // Elimina file fisico
                $absPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $oldDoc['file_path']);
                if (file_exists($absPath)) {
                    @unlink($absPath);
                }
                // Elimina record DB
                $docModel->delete($oldDocId);
            }
        }

        $docId = (new Document())->create($memberId, 'dm_certificate', (int)$course['year'], $docPathPublic);
        $cpModel->setCertificate((int)$courseId, $memberId, $docId);
        Helpers::addFlash('success','Attestato DM generato');
    } else {
        Helpers::addFlash('danger','Errore generazione attestato (template o permessi mancanti)');
    }
    Helpers::redirect('/courses/'.$courseId);
  }
  public function generateMass($courseId) {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    $course = (new Course())->find((int)$courseId);
    if (!$course) { http_response_code(404); echo 'Corso non trovato'; return; }
    $pdo = DB::conn();
    $assoc = $pdo->query('SELECT * FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $tplHtml = dirname(__DIR__) . '/templates/documents/dm_certificate.html';
    $tplRel = $assoc['dm_certificate_template_docx_path'] ?? null;
    $tplAbs = $tplRel ? str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $tplRel) : null;
    $list = (new CourseParticipant())->list((int)$courseId);
    $count = 0;
    foreach ($list as $mbr) {
      $vars = [
        'association_name' => $assoc ? $assoc['association_name'] : 'Associazione AP',
        'member_name' => $mbr['last_name'].' '.$mbr['first_name'],
        'member_email' => $mbr['email'] ?? '',
        'course_title' => str_replace("\r\n", "\n", $course['title']), // Normalizza newline
        'course_date' => $course['course_date'],
        'start_time' => $course['start_time'] ?? '',
        'end_time' => $course['end_time'] ?? '',
        'year' => (string)$course['year'],
        'date' => date('Y-m-d'),
      ];
      $basename = 'dm_' . preg_replace('/[^a-z0-9]+/i','_', $vars['member_name']) . '_' . (int)$courseId . '_' . date('dmYHis');
      $outputAbsBase = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 1) . '/../storage/documents/dm_certificate/'.$course['year'].'/'.$basename);
      $docPathPublic = null;
      if ($tplAbs && is_file($tplAbs)) {
        $mem = (new Membership())->getOrCreate((int)$mbr['member_id'], (int)$course['year']);
        $ext = strtolower(pathinfo($tplAbs, PATHINFO_EXTENSION));
        
        if ($ext === 'pdf') {
            // Option B: PDF Stamping
            $outPdf = $outputAbsBase . '.pdf';
            $opts = [
                'name_x' => (int)($assoc['dm_certificate_stamp_name_x'] ?? 100),
                'name_y' => (int)($assoc['dm_certificate_stamp_name_y'] ?? 120),
                'name_font_size' => (int)($assoc['dm_certificate_stamp_name_font_size'] ?? 16),
                'name_color' => $assoc['dm_certificate_stamp_name_color'] ?? '#000000',
                'name_font_family' => $assoc['dm_certificate_stamp_name_font_family'] ?? 'Arial',
                'name_bold' => $assoc['dm_certificate_stamp_name_bold'] ?? 1,
                
                'course_title_x' => (int)($assoc['dm_certificate_stamp_course_title_x'] ?? 100),
                'course_title_y' => (int)($assoc['dm_certificate_stamp_course_title_y'] ?? 140),
                'course_title_font_size' => (int)($assoc['dm_certificate_stamp_course_title_font_size'] ?? 16),
                'course_title_color' => $assoc['dm_certificate_stamp_course_title_color'] ?? '#000000',
                'course_title_font_family' => $assoc['dm_certificate_stamp_course_title_font_family'] ?? 'Arial',
                'course_title_bold' => $assoc['dm_certificate_stamp_course_title_bold'] ?? 1,
                'course_title_value' => $vars['course_title'], // Usa il valore normalizzato da $vars
    
                'date_x' => (int)($assoc['dm_certificate_stamp_date_x'] ?? 0),
                'date_y' => (int)($assoc['dm_certificate_stamp_date_y'] ?? 0),
                'date_font_size' => (int)($assoc['dm_certificate_stamp_date_font_size'] ?? 12),
                'date_color' => $assoc['dm_certificate_stamp_date_color'] ?? '#000000',
                'date_font_family' => $assoc['dm_certificate_stamp_date_font_family'] ?? 'Arial',
                'date_bold' => $assoc['dm_certificate_stamp_date_bold'] ?? 0,
                'date_value' => date('d/m/Y'),
    
                'year_x' => (int)($assoc['dm_certificate_stamp_year_x'] ?? 0),
                'year_y' => (int)($assoc['dm_certificate_stamp_year_y'] ?? 0),
                'year_font_size' => (int)($assoc['dm_certificate_stamp_year_font_size'] ?? 12),
                'year_color' => $assoc['dm_certificate_stamp_year_color'] ?? '#000000',
                'year_font_family' => $assoc['dm_certificate_stamp_year_font_family'] ?? 'Arial',
                'year_bold' => $assoc['dm_certificate_stamp_year_bold'] ?? 0,
                'year_value' => (string)$course['year'],
            ];
            PDFStampService::stampMembershipCertificate($tplAbs, $outPdf, $vars['member_name'], $mem['id'], $opts);
            if (is_file($outPdf)) {
               $docPathPublic = 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.pdf';
            }
        } else {
            // Option A: DOCX
            $vars2 = [
              'nome' => $vars['member_name'],
              'NOME' => $vars['member_name'],
              'te' => strval($mem['id']),
              'TE' => strval($mem['id']),
              'a' => (string)$course['year'],
              'A' => (string)$course['year'],
              'data' => date('d/m/Y'),
            ];
            $outPdf = $outputAbsBase . '.pdf';
            $rendered = DocxTemplateService::renderToPdf($tplAbs, $vars2, $outPdf);
            if ($rendered) {
              $docPathPublic = 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.pdf';
            } else {
              $outDocx = $outputAbsBase . '.docx';
              $docxOk = DocxTemplateService::renderToDocx($tplAbs, $vars2, $outDocx);
              if ($docxOk) {
                $docPathPublic = 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.docx';
              } else {
              $html = DocumentService::renderTemplate($tplHtml, $vars);
              $paths = DocumentService::saveDocument('dm_certificate', (int)$course['year'], $basename, $html);
              $docPathPublic = $paths['pdf']
                ? 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.pdf'
                : 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.html';
              }
            }
        }
      } else {
        $html = DocumentService::renderTemplate($tplHtml, $vars);
        $paths = DocumentService::saveDocument('dm_certificate', (int)$course['year'], $basename, $html);
        $docPathPublic = $paths['pdf']
          ? 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.pdf'
          : 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.html';
      }
      if ($docPathPublic) {
        $cpModel = new CourseParticipant();
        $oldDocId = $cpModel->getCertificateId($courseId, (int)$mbr['member_id']);
        if ($oldDocId) {
            $docModel = new Document();
            $oldDoc = $docModel->find($oldDocId);
            if ($oldDoc) {
                $absPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $oldDoc['file_path']);
                if (file_exists($absPath)) @unlink($absPath);
                $docModel->delete($oldDocId);
            }
        }

        $docId = (new Document())->create((int)$mbr['member_id'], 'dm_certificate', (int)$course['year'], $docPathPublic);
        $cpModel->setCertificate((int)$courseId, (int)$mbr['member_id'], $docId);
        $count++;
      } else {
        // Logga o notifica errore per questo utente?
        // Per ora silenzioso o potremmo raccogliere errori
      }
    }
    if ($count > 0) {
        Helpers::addFlash('success','Attestati DM generati: '.$count);
    } else {
        Helpers::addFlash('warning','Nessun attestato generato (verificare template)');
    }
    Helpers::redirect('/courses/'.$courseId);
  }
}
