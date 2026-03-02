<?php
namespace App\Services;
class DocumentService {
  public static function renderTemplate($templatePath, $vars) {
    $html = file_exists($templatePath) ? file_get_contents($templatePath) : '';
    foreach ($vars as $k=>$v) { $html = str_replace('{{'.strtoupper($k).'}}', $v, $html); }
    return $html;
  }
  public static function saveReceipt($year, $number, $html) {
    $dir = dirname(__DIR__, 1) . '/../storage/documents/receipts/' . $year;
    $dir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dir);
    if (!is_dir($dir)) { mkdir($dir, 0777, true); }
    $htmlPath = $dir . DIRECTORY_SEPARATOR . 'receipt_' . $number . '.html';
    file_put_contents($htmlPath, $html);
    $pdfPath = $dir . DIRECTORY_SEPARATOR . 'receipt_' . $number . '.pdf';
    if (class_exists('TCPDF')) {
       $pdf = new \TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
       $pdf->SetCreator('Gestionale');
      $pdf->setPrintHeader(false);
      $pdf->setPrintFooter(false);
      $pdf->SetMargins(0, 0, 0);
      $pdf->SetAutoPageBreak(TRUE, 0);
      $pdf->AddPage();
      $pdf->SetFont('dejavusans', '', 10);
      $pdf->writeHTML($html, true, false, true, false, '');
      $pdf->Output($pdfPath, 'F');
      return ['pdf'=>$pdfPath, 'html'=>$htmlPath];
    }
    return ['pdf'=>null, 'html'=>$htmlPath];
  }
  public static function saveDocument($type, $year, $basename, $html) {
    $dir = dirname(__DIR__, 1) . '/../storage/documents/' . $type . '/' . $year;
    $dir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dir);
    if (!is_dir($dir)) { mkdir($dir, 0777, true); }
    $htmlPath = $dir . DIRECTORY_SEPARATOR . $basename . '.html';
    file_put_contents($htmlPath, $html);
    $pdfPath = $dir . DIRECTORY_SEPARATOR . $basename . '.pdf';
    if (class_exists('TCPDF')) {
       $pdf = new \TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
       $pdf->SetCreator('Gestionale');
      $pdf->setPrintHeader(false);
      $pdf->setPrintFooter(false);
      $pdf->SetMargins(0, 0, 0);
      $pdf->SetAutoPageBreak(TRUE, 0);
      $pdf->AddPage();
      $pdf->SetFont('dejavusans', '', 10);
      $pdf->writeHTML($html, true, false, true, false, '');
      $pdf->Output($pdfPath, 'F');
      return ['pdf'=>$pdfPath, 'html'=>$htmlPath];
    }
    return ['pdf'=>null, 'html'=>$htmlPath];
  }
}
