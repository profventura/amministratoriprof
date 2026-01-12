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
use App\Services\PDFStampService;
class CertificatesController {
  public function generateMember() {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    $memberId = (int)($_POST['member_id'] ?? 0);
    $year = (int)($_POST['year'] ?? date('Y'));
    if ($memberId <= 0) { Helpers::addFlash('danger', 'Seleziona un socio'); Helpers::redirect('/members'); return; }
    $pdo = DB::conn();
    $assoc = $pdo->query('SELECT association_name, membership_certificate_template_path FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $mbr = (new Member())->find($memberId);
    $membership = (new Membership())->getOrCreate($memberId, $year);
    $vars = [
      'association_name' => $assoc ? $assoc['association_name'] : 'Associazione AP',
      'member_name' => $mbr ? ($mbr['first_name'].' '.$mbr['last_name']) : '',
      'member_email' => $mbr ? ($mbr['email'] ?? '') : '',
      'year' => (string)$year,
      'date' => date('Y-m-d'),
    ];
    $basename = 'certificate_' . preg_replace('/[^a-z0-9]+/i','_', $vars['member_name']);
    $outputAbsBase = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 1) . '/../storage/documents/membership_certificate/'.$year.'/'.$basename);
    $docPathPublic = null;
    $tplRel = $assoc['membership_certificate_template_path'] ?? null;
    $tplAbs = $tplRel ? str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $tplRel) : null;
    if ($tplAbs && is_file($tplAbs)) {
      $outPdf = $outputAbsBase . '.pdf';
      PDFStampService::stampMembershipCertificate($tplAbs, $outPdf, $vars['member_name'], $membership['id']);
      $docPathPublic = 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.pdf';
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
  public function generateMass() {
    Auth::require();
    if (!Auth::isAdmin()) { http_response_code(403); echo '403'; return; }
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    $year = (int)($_POST['year'] ?? date('Y'));
    $pdo = DB::conn();
    $rows = $pdo->prepare("SELECT mb.id, mb.first_name, mb.last_name, mb.email
      FROM memberships m JOIN members mb ON mb.id=m.member_id
      WHERE m.year=? AND m.status='regular' AND mb.deleted_at IS NULL
      ORDER BY mb.last_name, mb.first_name");
    $rows->execute([$year]);
    $list = $rows->fetchAll();
    $assoc = $pdo->query('SELECT association_name, membership_certificate_template_path FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $tplHtml = dirname(__DIR__) . '/templates/documents/membership_certificate.html';
    $tplRel = $assoc['membership_certificate_template_path'] ?? null;
    $tplAbs = $tplRel ? str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $tplRel) : null;
    $count = 0;
    foreach ($list as $mbr) {
      $vars = [
        'association_name' => $assoc ? $assoc['association_name'] : 'Associazione AP',
        'member_name' => $mbr['last_name'].' '.$mbr['first_name'],
        'member_email' => $mbr['email'] ?? '',
        'year' => (string)$year,
        'date' => date('Y-m-d'),
      ];
      $basename = 'certificate_' . preg_replace('/[^a-z0-9]+/i','_', $vars['member_name']);
      $outputAbsBase = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 1) . '/../storage/documents/membership_certificate/'.$year.'/'.$basename);
      $docPathPublic = null;
      if ($tplAbs && is_file($tplAbs)) {
        $membership = (new Membership())->getOrCreate((int)$mbr['id'], $year);
        $outPdf = $outputAbsBase . '.pdf';
        PDFStampService::stampMembershipCertificate($tplAbs, $outPdf, $vars['member_name'], $membership['id']);
        $docPathPublic = 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.pdf';
      } else {
        $html = DocumentService::renderTemplate($tplHtml, $vars);
        $paths = DocumentService::saveDocument('membership_certificate', $year, $basename, $html);
        $docPathPublic = $paths['pdf']
          ? 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.pdf'
          : 'storage/documents/membership_certificate/'.$year.'/'.$basename.'.html';
      }
      (new Document())->create((int)$mbr['id'], 'membership_certificate', $year, $docPathPublic);
      $count++;
    }
    Helpers::addFlash('success', 'Certificati generati: '.$count);
    Helpers::redirect('/memberships?year='.$year);
  }
}
