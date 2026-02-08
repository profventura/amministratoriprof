<?php
namespace App\Services;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\IOFactory;
class DocxTemplateService {
  private static function compileDocx($templateAbsPath, $vars, $outputDocxPath) {
    if (!class_exists('ZipArchive')) { return null; }
    $dir = dirname($outputDocxPath);
    if (!is_dir($dir)) { mkdir($dir, 0777, true); }
    
    try {
        // Use CustomTemplateProcessor for better broken macro handling
        $templateProcessor = new CustomTemplateProcessor($templateAbsPath);
        
        // Check if we need to switch delimiters? 
        // For now we assume ${} as per project settings, but the class allows switching.
        // If the user really wants #{}, we could detect it or config it.
        // But the previous "generate_certificate.php" used #{}, so maybe the user's template HAS #{}.
        // Let's try to sniff or just stick to ${} as the UI says.
        
        foreach ($vars as $k => $v) {
            $templateProcessor->setValue($k, $v);
            // $templateProcessor->setValue(strtoupper($k), $v); // Only if needed
        }
        
        $templateProcessor->saveAs($outputDocxPath);
        return is_file($outputDocxPath) ? $outputDocxPath : null;
    } catch (\Throwable $e) {
        error_log("DocxTemplateService error: " . $e->getMessage());
        return null;
    }
  }
  private static function convertToPdfWithLibreOffice($inputDocx, $outputPdf) {
    $outDir = dirname($outputPdf);
    if (!is_dir($outDir)) { mkdir($outDir, 0777, true); }
    $cmd = 'soffice --headless --convert-to pdf --outdir ' . escapeshellarg($outDir) . ' ' . escapeshellarg($inputDocx);
    try { @shell_exec($cmd); } catch (\Throwable $e) { return null; }
    $converted = $outDir . DIRECTORY_SEPARATOR . pathinfo($inputDocx, PATHINFO_FILENAME) . '.pdf';
    if (!is_file($converted)) { return null; }
    if ($converted !== $outputPdf) { @rename($converted, $outputPdf); }
    return $outputPdf;
  }
  public static function renderToPdf($templateAbsPath, $vars, $outputAbsPath) {
    if (!class_exists('ZipArchive')) { return null; }
    $dir = dirname($outputAbsPath);
    if (!is_dir($dir)) { mkdir($dir, 0777, true); }
    $tmpDocx = $outputAbsPath . '.docx';
    // Step 1: compile DOCX by raw XML replacement (robust for placeholders in shapes/headers/footers)
    $compiled = self::compileDocx($templateAbsPath, $vars, $tmpDocx);
    if (!$compiled) { return null; }
    // Step 2: try high-fidelity conversion via LibreOffice if available
    $lo = self::convertToPdfWithLibreOffice($compiled, $outputAbsPath);
    if ($lo && is_file($outputAbsPath)) { @unlink($tmpDocx); return $outputAbsPath; }
    // Step 3: fallback to PhpWord+DOMPDF (requires dompdf+gd)
    if (!class_exists('\\Dompdf\\Dompdf')) { 
      error_log("DOMPDF class not found");
      @unlink($tmpDocx); 
      return null; 
    }
    if (!extension_loaded('gd')) { 
      error_log("GD extension not loaded");
      @unlink($tmpDocx); 
      return null; 
    }

    try {
      Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
      $vendorPath = dirname(__DIR__, 2) . '/vendor';
      // Use the exact path that worked in the test script
      Settings::setPdfRendererPath($vendorPath . '/dompdf/dompdf');
      
      if (!is_file($tmpDocx)) {
        throw new \Exception("Compiled DOCX not found: " . $tmpDocx);
      }

      $phpWord = IOFactory::load($tmpDocx);
      $writer = IOFactory::createWriter($phpWord, 'PDF');
      $writer->save($outputAbsPath);
    } catch (\Throwable $e) {
      error_log("PHPWord PDF Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
      if (is_file($tmpDocx)) { @unlink($tmpDocx); }
      return null;
    }
    if (is_file($tmpDocx)) { @unlink($tmpDocx); }
    return is_file($outputAbsPath) ? $outputAbsPath : null;
  }
  public static function renderToDocx($templateAbsPath, $vars, $outputDocxPath) {
    if (!class_exists('ZipArchive')) { return null; }
    $dir = dirname($outputDocxPath);
    if (!is_dir($dir)) { mkdir($dir, 0777, true); }
    return self::compileDocx($templateAbsPath, $vars, $outputDocxPath);
  }
}
