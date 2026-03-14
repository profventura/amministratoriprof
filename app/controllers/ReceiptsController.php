<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Helpers;
use App\Core\DB;
use App\Models\Document;
use App\Services\DocumentService;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReceiptsController {
  public function index() {
    Auth::require();
    $pdo = DB::conn();
    $year = (int)($_GET['year'] ?? date('Y'));
    $stmt = $pdo->prepare('SELECT p.id, p.member_id, p.payment_date, p.amount, p.method, p.notes, p.receipt_number, p.receipt_year,
                                  mb.first_name, mb.last_name, mb.email
                           FROM payments p JOIN members mb ON mb.id=p.member_id
                           WHERE p.receipt_year=? ORDER BY p.payment_date DESC, p.id DESC');
    $stmt->execute([$year]);
    $rows = $stmt->fetchAll();
    Helpers::view('receipts/index', ['title'=>'Ricevute '.$year,'rows'=>$rows,'year'=>$year]);
  }
  public function download($id) {
    Auth::require();
    $pdo = DB::conn();
    $p = $pdo->prepare('SELECT * FROM payments WHERE id=?');
    $p->execute([(int)$id]);
    $pay = $p->fetch();
    if (!$pay) { http_response_code(404); echo 'Pagamento non trovato'; return; }
    $number = $pay['receipt_number'];
    $year = (int)$pay['receipt_year'];
    $docModel = new Document();
    $doc = $docModel->findReceiptByYearNumber($year, $number);
    if (!$doc) {
      $pdf = 'storage/documents/receipts/'.$year.'/receipt_'.$number.'.pdf';
      $html = 'storage/documents/receipts/'.$year.'/receipt_'.$number.'.html';
      $chosen = null;
      $absPdf = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $pdf;
      $absHtml = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $html;
      if (is_file($absPdf)) { $chosen = $pdf; }
      elseif (is_file($absHtml)) { $chosen = $html; }
      if ($chosen) {
        $docId = $docModel->create((int)$pay['member_id'], 'receipt', $year, $chosen);
        Helpers::redirect('/documents/'.$docId.'/download'); return;
      }
      Helpers::addFlash('danger', 'File ricevuta non presente: rigenera la ricevuta');
      Helpers::redirect('/receipts?year='.$year); return;
    }
    Helpers::redirect('/documents/'.$doc['id'].'/download');
  }
  public function regenerate($id) {
    Auth::require();
    $pdo = DB::conn();
    // Recupera pagamento e socio con indirizzo e CF
    $p = $pdo->prepare('SELECT p.*, mb.first_name, mb.last_name, mb.address, mb.city, mb.province, mb.zip_code, mb.tax_code, mb.email 
                        FROM payments p 
                        JOIN members mb ON mb.id=p.member_id 
                        WHERE p.id=?');
    $p->execute([(int)$id]);
    $pay = $p->fetch();
    
    if (!$pay) { 
        Helpers::addFlash('danger', 'Pagamento non trovato');
        Helpers::redirect('/receipts');
        return; 
    }

    $year = (int)$pay['receipt_year'];
    $number = $pay['receipt_number'];

    // Dati per la stampa
    $memberName = strtoupper($pay['first_name'] . ' ' . $pay['last_name']);
    $memberAddress = trim(($pay['address'] ?? '') . "\n" . ($pay['zip_code'] ?? '') . ' ' . ($pay['city'] ?? '') . ' ' . ($pay['province'] ?? ''));
    $memberCF = strtoupper($pay['tax_code'] ?? '');
    $amount = '€ ' . number_format((float)$pay['amount'], 2, ',', '.');
    $description = $pay['notes'] ?? 'Quota Associativa'; // Usa note o fallback
    $date = date('d/m/Y', strtotime($pay['payment_date']));
    $fullNumber = $year . '/' . str_pad($number, 3, '0', STR_PAD_LEFT);

    // Controlla se esiste template PDF in settings
    $settings = $pdo->query('SELECT * FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $tplRel = $settings['receipt_template_path'] ?? null;
    $tplAbs = $tplRel ? str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $tplRel) : null;

    $outputRel = 'storage/documents/receipts/'.$year.'/receipt_'.$number.'.pdf';
    $outputAbs = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $outputRel);
    
    $generated = false;

    if ($tplAbs && is_file($tplAbs) && strtolower(pathinfo($tplAbs, PATHINFO_EXTENSION)) === 'pdf') {
        // Usa PDFStampService
        $opts = [];
        // Mappa campi DB settings -> opts
        $fields = ['receipt_number', 'receipt_date', 'member_name', 'member_address', 'member_cf', 'amount', 'description'];
        
        foreach ($fields as $f) {
            $prefix = "receipt_stamp_{$f}";
            $opts["{$f}_x"] = (int)($settings["{$prefix}_x"] ?? 0);
            $opts["{$f}_y"] = (int)($settings["{$prefix}_y"] ?? 0);
            $opts["{$f}_font_size"] = (int)($settings["{$prefix}_font_size"] ?? 12);
            $opts["{$f}_color"] = $settings["{$prefix}_color"] ?? '#000000';
            $opts["{$f}_font_family"] = $settings["{$prefix}_font_family"] ?? 'Arial';
            $opts["{$f}_bold"] = !empty($settings["{$prefix}_bold"]);
        }

        // Assegna valori
        $opts['receipt_number_value'] = $fullNumber;
        $opts['receipt_date_value'] = $date;
        $opts['member_name_value'] = $memberName;
        $opts['member_address_value'] = $memberAddress;
        $opts['member_cf_value'] = $memberCF;
        $opts['amount_value'] = $amount;
        $opts['description_value'] = $description;

        // Genera
        if (\App\Services\PDFStampService::stampGeneric($tplAbs, $outputAbs, $opts)) {
            $generated = true;
        }
    }

    if (!$generated) {
        // Logica HTML Template (nuova gestione)
        $tplHtmlPath = dirname(__DIR__, 1) . '/templates/documents/receipt_template.html';
        if (file_exists($tplHtmlPath)) {
            $html = file_get_contents($tplHtmlPath);
            
            // Dati per placeholder
            $billingDetails = "<strong>$memberName</strong><br>" . nl2br($memberAddress) . "<br>C.F.: $memberCF";
            
            $placeholders = [
                '{{receipt_number}}' => $number,
                '{{year}}' => $year,
                '{{receipt_date}}' => $date,
                '{{billing_details}}' => $billingDetails,
                '{{description}}' => 'QUOTA ANNUALE ISCRIZIONE', // O $description
                '{{item_description}}' => $description,
                '{{amount}}' => number_format((float)$pay['amount'], 2, ',', '.'),
                '{{payment_date}}' => $date
            ];
            
            $html = str_replace(array_keys($placeholders), array_values($placeholders), $html);
            
            // Gestione Logo per PDF (path assoluto)
            $logoPath = dirname(__DIR__, 2) . '/public/images/logos/ap_logo.jpg';
            $logoPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $logoPath);
            if (file_exists($logoPath)) {
                 $html = str_replace('{{logo_src}}', $logoPath, $html);
                 $html = str_replace('/amministratoriprof/public/images/logos/ap_logo.jpg', $logoPath, $html);
                 $html = str_replace('http://localhost/amministratoriprof/public/images/logos/ap_logo.jpg', $logoPath, $html);
                 $html = str_replace('../public/images/logos/ap_logo.jpg', $logoPath, $html);
            }
            
            // Genera PDF con Dompdf o mPDF o salva HTML e poi converti
            // Qui usiamo DocumentService::saveReceiptFromHtml che userà Dompdf
            $pdfRelPath = 'storage/documents/receipts/'.$year.'/receipt_'.$number.'.pdf';
            $pdfAbsPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $pdfRelPath);
            
            $dir = dirname($pdfAbsPath);
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            
            // Usa Dompdf (assicurati che sia installato, altrimenti salva HTML)
            // Se non abbiamo Dompdf pronto nel service, salviamo l'HTML e basta?
            // DocumentService::generatePdfFromHtml($html, $pdfAbsPath);
            
            // Quick implementation Dompdf
             if (class_exists(Dompdf::class)) {
                 $options = new Options();
                 $options->set('isHtml5ParserEnabled', true);
                 $options->set('isRemoteEnabled', true);
                 
                 $dompdf = new Dompdf($options);
                 $dompdf->loadHtml($html);
                 $dompdf->setPaper('A4', 'portrait');
                 $dompdf->render();
                 
                 file_put_contents($pdfAbsPath, $dompdf->output());
                 
                $generated = true;
                $outputRel = $pdfRelPath;
            } else {
                // Fallback: salva HTML
                $htmlRelPath = 'storage/documents/receipts/'.$year.'/receipt_'.$number.'.html';
                $htmlAbsPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $htmlRelPath);
                file_put_contents($htmlAbsPath, $html);
                $outputRel = $htmlRelPath;
                $generated = true;
            }
        }
    }

    if (!$generated) {
        // Fallback vecchio (legacy)
        if (file_exists(dirname(__DIR__) . '/templates/documents/receipt.html')) {
            $tplHtml = dirname(__DIR__) . '/templates/documents/receipt.html';
            
            $vars = [
              'association_name' => $settings['association_name'] ?? 'Associazione AP',
              'receipt_number' => $fullNumber,
              'receipt_year' => (string)$year,
              'date' => $date,
              'member_name' => $memberName,
              'member_email' => $pay['email'] ?? '',
              'year' => (string)$year,
              'method' => $pay['method'],
              'amount' => $amount,
              'notes' => $description,
            ];
            
            $htmlContent = \App\Services\DocumentService::renderTemplate($tplHtml, $vars);
            $paths = \App\Services\DocumentService::saveReceipt($year, $number, $htmlContent);
            if ($paths['pdf']) {
                $outputRel = $paths['pdf'];
                $generated = true;
            } elseif ($paths['html']) {
                $outputRel = $paths['html'];
                $generated = true;
            }
        }
    }

    if ($generated) {
        // Aggiorna o crea entry in documents
        $docModel = new \App\Models\Document();
        $doc = $docModel->findReceiptByYearNumber($year, $number);
        
        if ($doc) {
            // Aggiorna path se cambiato
            $pdo->prepare("UPDATE documents SET file_path=?, created_at=NOW() WHERE id=?")->execute([$outputRel, $doc['id']]);
            $docId = $doc['id'];
        } else {
            $docId = $docModel->create((int)$pay['member_id'], 'receipt', $year, $outputRel);
        }
        
        Helpers::addFlash('success', 'Ricevuta rigenerata');
        Helpers::redirect('/receipts'); // Torna alla lista
    } else {
        Helpers::addFlash('danger', 'Errore generazione ricevuta');
        Helpers::redirect('/receipts');
    }
  }
}
