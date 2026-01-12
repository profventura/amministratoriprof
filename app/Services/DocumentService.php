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
    if (class_exists('\\Dompdf\\Dompdf')) {
      $dompdf = new \Dompdf\Dompdf();
      $dompdf->loadHtml($html);
      $dompdf->setPaper('A4', 'portrait');
      $dompdf->render();
      file_put_contents($pdfPath, $dompdf->output());
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
    if (class_exists('\\Dompdf\\Dompdf')) {
      $dompdf = new \Dompdf\Dompdf();
      $dompdf->loadHtml($html);
      $dompdf->setPaper('A4', 'portrait');
      $dompdf->render();
      file_put_contents($pdfPath, $dompdf->output());
      return ['pdf'=>$pdfPath, 'html'=>$htmlPath];
    }
    return ['pdf'=>null, 'html'=>$htmlPath];
  }
}
