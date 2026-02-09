<?php
namespace App\Services;
use setasign\Fpdi\Fpdi;
class PDFStampService {
  public static function stampMembershipCertificate($templateAbsPath, $outputAbsPath, $name, $number, $opts = []) {
    // Configurazione Nome
    $xName = $opts['name_x'] ?? 100;
    $yName = $opts['name_y'] ?? 120;
    $fsName = $opts['name_font_size'] ?? 16;
    $cName = self::hex2rgb($opts['name_color'] ?? '#000000');
    $fName = $opts['name_font_family'] ?? 'Arial';
    $bName = !empty($opts['name_bold']) ? 'B' : '';

    // Configurazione Numero
    $xNum  = $opts['number_x'] ?? 100;
    $yNum  = $opts['number_y'] ?? 140;
    $fsNum = $opts['number_font_size'] ?? 16;
    $cNum = self::hex2rgb($opts['number_color'] ?? '#000000');
    $fNum = $opts['number_font_family'] ?? 'Arial';
    $bNum = !empty($opts['number_bold']) ? 'B' : '';

    // Configurazione Data (opzionale)
    $xDate = $opts['date_x'] ?? 0;
    $yDate = $opts['date_y'] ?? 0;
    $fsDate = $opts['date_font_size'] ?? 12;
    $cDate = self::hex2rgb($opts['date_color'] ?? '#000000');
    $fDate = $opts['date_font_family'] ?? 'Arial';
    $bDate = !empty($opts['date_bold']) ? 'B' : '';
    $dateVal = $opts['date_value'] ?? date('d/m/Y');

    // Configurazione Anno (opzionale)
    $xYear = $opts['year_x'] ?? 0;
    $yYear = $opts['year_y'] ?? 0;
    $fsYear = $opts['year_font_size'] ?? 12;
    $cYear = self::hex2rgb($opts['year_color'] ?? '#000000');
    $fYear = $opts['year_font_family'] ?? 'Arial';
    $bYear = !empty($opts['year_bold']) ? 'B' : '';
    $yearVal = $opts['year_value'] ?? date('Y');

    // Configurazione Argomento/Titolo Corso (opzionale)
    $xCourse = $opts['course_title_x'] ?? 0;
    $yCourse = $opts['course_title_y'] ?? 0;
    $fsCourse = $opts['course_title_font_size'] ?? 16;
    $cCourse = self::hex2rgb($opts['course_title_color'] ?? '#000000');
    $fCourse = $opts['course_title_font_family'] ?? 'Arial';
    $bCourse = !empty($opts['course_title_bold']) ? 'B' : '';
    $courseVal = $opts['course_title_value'] ?? '';

    try {
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($templateAbsPath);
        $tpl = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tpl);
        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
        
        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
        $pdf->useTemplate($tpl);

        // DEBUG GRID (Se richiesto)
        if (!empty($opts['debug_grid'])) {
            $pdf->SetDrawColor(200, 200, 200); // Grigio chiaro
            $pdf->SetTextColor(255, 0, 0); // Rosso
            $pdf->SetFont('Arial', '', 6);
            
            // Griglia ogni 10mm
            $w = $size['width'];
            $h = $size['height'];
            for ($x = 0; $x < $w; $x += 10) {
                $pdf->Line($x, 0, $x, $h);
                $pdf->Text($x+1, 5, $x);
            }
            for ($y = 0; $y < $h; $y += 10) {
                $pdf->Line(0, $y, $w, $y);
                $pdf->Text(1, $y+2, $y);
            }
            // Origine
            $pdf->SetTextColor(0, 0, 255);
            $pdf->Text(2, 8, "ORIGIN (0,0)");
        }

        // Registra font personalizzati
        $pdf->AddFont('gillsansmt', '', 'gillsansmt.php');
        $pdf->AddFont('gillsansmt', 'B', 'gillsansmtb.php'); // Usa il font bold specifico
        $pdf->AddFont('gillsansmt', 'I', 'gillsansmt.php'); // Usa lo stesso file per il corsivo (fallback)

        $mapFont = function($f) {
            $f = strtolower($f ?? '');
            if (strpos($f, 'times') !== false) return 'Times';
            if (strpos($f, 'courier') !== false) return 'Courier';
            if (strpos($f, 'helvetica') !== false) return 'Helvetica';
            if (strpos($f, 'gill') !== false) return 'gillsansmt'; 
            return 'Arial';
        };

        // Helper per stampa centrata (X,Y sono il centro del testo)
        $printCentered = function($text, $x, $y, $font, $style, $size, $color) use ($pdf, $mapFont) {
            $font = $mapFont($font);
            if ($x > 0 && $y > 0) {
                $pdf->SetFont($font, $style, $size);
                $pdf->SetTextColor($color[0], $color[1], $color[2]);
                $w = $pdf->GetStringWidth($text);
                
                // Se il testo è multiriga (contiene \n), dobbiamo gestirlo
                if (strpos($text, "\n") !== false) {
                    $lines = explode("\n", $text);
                    $lineHeight = $size * 0.4; // Approssimazione line height in mm
                    $totalHeight = count($lines) * $lineHeight;
                    $startY = $y - ($totalHeight / 2) + ($lineHeight / 2); // Centrato verticalmente
                    
                    foreach ($lines as $i => $line) {
                        $wLine = $pdf->GetStringWidth($line);
                        $pdf->SetXY($x - ($wLine / 2), $startY + ($i * $lineHeight));
                        $pdf->Write(0, $line);
                    }
                } else {
                    // Testo singola riga
                    $pdf->SetXY($x - ($w / 2), $y);
                    $pdf->Write(0, $text);
                }
            }
        };

        // Stampa Nome
        $printCentered($name, $xName, $yName, $fName, $bName, $fsName, $cName);

        // Stampa Numero
        $printCentered(strval($number), $xNum, $yNum, $fNum, $bNum, $fsNum, $cNum);

        // Stampa Data
        $printCentered($dateVal, $xDate, $yDate, $fDate, $bDate, $fsDate, $cDate);

        // Stampa Anno
        $printCentered(strval($yearVal), $xYear, $yYear, $fYear, $bYear, $fsYear, $cYear);

        // Stampa Argomento/Titolo Corso
        $printCentered($courseVal, $xCourse, $yCourse, $fCourse, $bCourse, $fsCourse, $cCourse);

        $dir = dirname($outputAbsPath);
        if (!is_dir($dir)) { mkdir($dir, 0777, true); }
        $pdf->Output($outputAbsPath, 'F');
        return $outputAbsPath;
    } catch (\Throwable $e) {
        // Fallback: prova a riparare il PDF con LibreOffice se possibile, altrimenti ritorna false
        return self::tryRepairPdf($templateAbsPath, $outputAbsPath, $name, $number, $opts);
    }
  }

  private static function hex2rgb($hex) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    return [$r, $g, $b];
  }

  private static function tryRepairPdf($tpl, $out, $name, $num, $opts) {
      // Se abbiamo LibreOffice, proviamo a convertire il PDF in PDF 1.4
      // "soffice --convert-to pdf" su un pdf spesso lo normalizza
      $dir = dirname($out);
      $repaired = $dir . '/repaired_' . basename($tpl);
      // ... logica di repair omessa per brevità, ritorniamo false per ora
      // Se servisse, si può implementare usando lo stesso metodo di DocxTemplateService
      return false; 
  }
}
