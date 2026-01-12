<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Helpers;
use App\Core\DB;
use App\Models\Course;
use App\Models\CourseParticipant;
use App\Models\Member;
use App\Models\Document;
use App\Services\DocumentService;
class DMCertificatesController {
  public function generateSingle($courseId) {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    $memberId = (int)($_POST['member_id'] ?? 0);
    $course = (new Course())->find((int)$courseId);
    if (!$course || $memberId <= 0) { Helpers::addFlash('danger','Dati non validi'); Helpers::redirect('/courses/'.$courseId); return; }
    $pdo = DB::conn();
    $assoc = $pdo->query('SELECT association_name FROM settings ORDER BY id DESC LIMIT 1')->fetch();
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
    $tpl = dirname(__DIR__) . '/templates/documents/dm_certificate.html';
    $html = DocumentService::renderTemplate($tpl, $vars);
    $basename = 'dm_' . preg_replace('/[^a-z0-9]+/i','_', $vars['member_name']) . '_' . (int)$courseId;
    $paths = DocumentService::saveDocument('dm_certificate', (int)$course['year'], $basename, $html);
    $docPathPublic = $paths['pdf']
      ? 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.pdf'
      : 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.html';
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
    $assoc = $pdo->query('SELECT association_name FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $tpl = dirname(__DIR__) . '/templates/documents/dm_certificate.html';
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
      $html = DocumentService::renderTemplate($tpl, $vars);
      $basename = 'dm_' . preg_replace('/[^a-z0-9]+/i','_', $vars['member_name']) . '_' . (int)$courseId;
      $paths = DocumentService::saveDocument('dm_certificate', (int)$course['year'], $basename, $html);
      $docPathPublic = $paths['pdf']
        ? 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.pdf'
        : 'storage/documents/dm_certificate/'.$course['year'].'/'.$basename.'.html';
      $docId = (new Document())->create((int)$mbr['member_id'], 'dm_certificate', (int)$course['year'], $docPathPublic);
      (new CourseParticipant())->setCertificate((int)$courseId, (int)$mbr['member_id'], $docId);
      $count++;
    }
    Helpers::addFlash('success','Attestati DM generati: '.$count);
    Helpers::redirect('/courses/'.$courseId);
  }
}
