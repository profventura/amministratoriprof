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
      'course_title' => $course['title'],
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
            'name_x' => (int)($assoc['certificate_stamp_name_x'] ?? 100),
            'name_y' => (int)($assoc['certificate_stamp_name_y'] ?? 120),
            'name_font_size' => (int)($assoc['certificate_stamp_name_font_size'] ?? 16),
            'name_color' => $assoc['certificate_stamp_name_color'] ?? '#000000',
            'name_font_family' => $assoc['certificate_stamp_name_font_family'] ?? 'Arial',
            
            'number_x' => (int)($assoc['certificate_stamp_number_x'] ?? 100),
            'number_y' => (int)($assoc['certificate_stamp_number_y'] ?? 140),
            'number_font_size' => (int)($assoc['certificate_stamp_number_font_size'] ?? 16),
            'number_color' => $assoc['certificate_stamp_number_color'] ?? '#000000',
            'number_font_family' => $assoc['certificate_stamp_number_font_family'] ?? 'Arial',

            'date_x' => (int)($assoc['certificate_stamp_date_x'] ?? 0),
            'date_y' => (int)($assoc['certificate_stamp_date_y'] ?? 0),
            'date_font_size' => (int)($assoc['certificate_stamp_date_font_size'] ?? 12),
            'date_color' => $assoc['certificate_stamp_date_color'] ?? '#000000',
            'date_font_family' => $assoc['certificate_stamp_date_font_family'] ?? 'Arial',
            'date_value' => date('d/m/Y'),

            'year_x' => (int)($assoc['certificate_stamp_year_x'] ?? 0),
            'year_y' => (int)($assoc['certificate_stamp_year_y'] ?? 0),
            'year_font_size' => (int)($assoc['certificate_stamp_year_font_size'] ?? 12),
            'year_color' => $assoc['certificate_stamp_year_color'] ?? '#000000',
            'year_font_family' => $assoc['certificate_stamp_year_font_family'] ?? 'Arial',
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
    $docId = (new Document())->create($memberId, 'dm_certificate', (int)$course['year'], $docPathPublic);
    (new CourseParticipant())->setCertificate((int)$courseId, $memberId, $docId);
    Helpers::addFlash('success','Attestato DM generato');
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
        'course_title' => $course['title'],
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
                'name_x' => (int)($assoc['certificate_stamp_name_x'] ?? 100),
                'name_y' => (int)($assoc['certificate_stamp_name_y'] ?? 120),
                'name_font_size' => (int)($assoc['certificate_stamp_name_font_size'] ?? 16),
                'name_color' => $assoc['certificate_stamp_name_color'] ?? '#000000',
                'name_font_family' => $assoc['certificate_stamp_name_font_family'] ?? 'Arial',
                
                'number_x' => (int)($assoc['certificate_stamp_number_x'] ?? 100),
                'number_y' => (int)($assoc['certificate_stamp_number_y'] ?? 140),
                'number_font_size' => (int)($assoc['certificate_stamp_number_font_size'] ?? 16),
                'number_color' => $assoc['certificate_stamp_number_color'] ?? '#000000',
                'number_font_family' => $assoc['certificate_stamp_number_font_family'] ?? 'Arial',
    
                'date_x' => (int)($assoc['certificate_stamp_date_x'] ?? 0),
                'date_y' => (int)($assoc['certificate_stamp_date_y'] ?? 0),
                'date_font_size' => (int)($assoc['certificate_stamp_date_font_size'] ?? 12),
                'date_color' => $assoc['certificate_stamp_date_color'] ?? '#000000',
                'date_font_family' => $assoc['certificate_stamp_date_font_family'] ?? 'Arial',
                'date_value' => date('d/m/Y'),
    
                'year_x' => (int)($assoc['certificate_stamp_year_x'] ?? 0),
                'year_y' => (int)($assoc['certificate_stamp_year_y'] ?? 0),
                'year_font_size' => (int)($assoc['certificate_stamp_year_font_size'] ?? 12),
                'year_color' => $assoc['certificate_stamp_year_color'] ?? '#000000',
                'year_font_family' => $assoc['certificate_stamp_year_font_family'] ?? 'Arial',
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
      $docId = (new Document())->create((int)$mbr['member_id'], 'dm_certificate', (int)$course['year'], $docPathPublic);
      (new CourseParticipant())->setCertificate((int)$courseId, (int)$mbr['member_id'], $docId);
      $count++;
    }
    Helpers::addFlash('success','Attestati DM generati: '.$count);
    Helpers::redirect('/courses/'.$courseId);
  }
}
