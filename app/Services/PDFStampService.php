<?php
namespace App\Services;
use setasign\Fpdi\Fpdi;
class PDFStampService {
  public static function stampMembershipCertificate($templateAbsPath, $outputAbsPath, $name, $number, $opts = []) {
    $xName = $opts['name_x'] ?? 100; // mm
    $yName = $opts['name_y'] ?? 120; // mm
    $xNum  = $opts['number_x'] ?? 100; // mm
    $yNum  = $opts['number_y'] ?? 140; // mm
    $fontSize = $opts['font_size'] ?? 16;
    $pdf = new Fpdi();
    $pdf->setSourceFile($templateAbsPath);
    $tpl = $pdf->importPage(1);
    $size = $pdf->getTemplateSize($tpl);
    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
    $pdf->useTemplate($tpl);
    $pdf->SetTextColor(220, 0, 0);
    if (method_exists($pdf, 'SetFont')) { $pdf->SetFont('Arial', 'B', $fontSize); }
    $pdf->SetXY($xName, $yName);
    $pdf->Write(0, $name);
    $pdf->SetXY($xNum, $yNum);
    $pdf->Write(0, strval($number));
    $dir = dirname($outputAbsPath);
    if (!is_dir($dir)) { mkdir($dir, 0777, true); }
    $pdf->Output($outputAbsPath, 'F');
    return $outputAbsPath;
  }
}
