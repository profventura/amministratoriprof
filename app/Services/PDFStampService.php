<?php
namespace App\Services;
use setasign\Fpdi\Fpdi;
class PDFStampService {
  public static function stampGeneric($templateAbsPath, $outputAbsPath, $opts = []) {
    // Estraiamo i campi speciali standard se presenti, altrimenti usiamo solo opts
    // Questo metodo sostituisce stampMembershipCertificate rendendolo generico
    
    // Mappa campi opzionali se non presenti in opts
    // (Non serve se chi chiama passa tutto in opts con _x, _y, _value)
    
    try {
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($templateAbsPath);
        $tpl = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tpl);
        
        // Determina orientamento: usa quello forzato in $opts se presente, altrimenti auto-rileva
        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
        if (isset($opts['orientation']) && in_array($opts['orientation'], ['P', 'L'])) {
            $orientation = $opts['orientation'];
        }
        
        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
        $pdf->useTemplate($tpl);
        $pdf->SetAutoPageBreak(false); // IMPORTANTE: evita che FPDF crei nuove pagine se scriviamo in fondo


        // DEBUG GRID
        if (!empty($opts['debug_grid'])) {
            $pdf->SetDrawColor(200, 200, 200); 
            $pdf->SetTextColor(255, 0, 0); 
            $pdf->SetFont('Arial', '', 6);
            
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
            $pdf->SetTextColor(0, 0, 255);
            $pdf->Text(2, 8, "ORIGIN (0,0)");
        }

        // Registra font
        $pdf->AddFont('gillsansmt', '', 'gillsansmt.php');
        $pdf->AddFont('gillsansmt', 'B', 'gillsansmtb.php');
        $pdf->AddFont('gillsansmt', 'I', 'gillsansmt.php');

        $mapFont = function($f) {
            $f = strtolower($f ?? '');
            if (strpos($f, 'times') !== false) return 'Times';
            if (strpos($f, 'courier') !== false) return 'Courier';
            if (strpos($f, 'helvetica') !== false) return 'Helvetica';
            if (strpos($f, 'gill') !== false) return 'gillsansmt'; 
            return 'Arial';
        };

        // Helper stampa centrata
        $printCentered = function($text, $x, $y, $font, $style, $size, $color) use ($pdf, $mapFont) {
            $font = $mapFont($font);
            if ($x > 0 && $y > 0) {
                $pdf->SetFont($font, $style, $size);
                $pdf->SetTextColor($color[0], $color[1], $color[2]);
                $w = $pdf->GetStringWidth($text);
                
                if (strpos($text, "\n") !== false) {
                    $lines = explode("\n", $text);
                    $lineHeight = $size * 0.4; 
                    $totalHeight = count($lines) * $lineHeight;
                    $startY = $y - ($totalHeight / 2) + ($lineHeight / 2);
                    
                    foreach ($lines as $i => $line) {
                        $wLine = $pdf->GetStringWidth($line);
                        $pdf->SetXY($x - ($wLine / 2), $startY + ($i * $lineHeight));
                        $pdf->Write(0, $line);
                    }
                } else {
                    $pdf->SetXY($x - ($w / 2), $y);
                    $pdf->Write(0, $text);
                }
            }
        };

        // Identifica quali campi stampare
        // Cerchiamo le chiavi che finiscono con _x in $opts e assumiamo esista un corrispondente _value
        foreach ($opts as $key => $val) {
            if (str_ends_with($key, '_x')) {
                $field = substr($key, 0, -2); // es. 'name' da 'name_x'
                
                // Se non c'è coordinata Y, salta
                if (empty($opts["{$field}_y"])) continue;
                
                $x = (int)$val;
                $y = (int)$opts["{$field}_y"];
                $fs = (int)($opts["{$field}_font_size"] ?? 12);
                $c = self::hex2rgb($opts["{$field}_color"] ?? '#000000');
                $f = $opts["{$field}_font_family"] ?? 'Arial';
                $b = !empty($opts["{$field}_bold"]) ? 'B' : '';
                
                // Valore: cerca field_value, poi field (se passato direttamente)
                $text = '';
                if (isset($opts["{$field}_value"])) $text = $opts["{$field}_value"];
                elseif (isset($opts[$field]) && !is_array($opts[$field])) $text = $opts[$field];
                
                if ($text !== '') {
                    $printCentered($text, $x, $y, $f, $b, $fs, $c);
                }
            }
        }

        $dir = dirname($outputAbsPath);
        if (!is_dir($dir)) { mkdir($dir, 0777, true); }
        $pdf->Output($outputAbsPath, 'F');
        return $outputAbsPath;
    } catch (\Throwable $e) {
        return false;
    }
  }

  // Wrapper per compatibilità (mantiene firma metodo vecchio ma usa quello generico)
  public static function stampMembershipCertificate($templateAbsPath, $outputAbsPath, $name, $number, $opts = []) {
      $opts['name_value'] = $name;
      $opts['number_value'] = $number;
      return self::stampGeneric($templateAbsPath, $outputAbsPath, $opts);
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
