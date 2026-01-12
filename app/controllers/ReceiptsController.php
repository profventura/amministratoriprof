<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Helpers;
use App\Core\DB;
use App\Models\Document;
use App\Services\DocumentService;
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
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    $pdo = DB::conn();
    $p = $pdo->prepare('SELECT p.*, mb.first_name, mb.last_name, mb.email FROM payments p JOIN members mb ON mb.id=p.member_id WHERE p.id=?');
    $p->execute([(int)$id]);
    $pay = $p->fetch();
    if (!$pay) { http_response_code(404); echo 'Pagamento non trovato'; return; }
    $assoc = $pdo->query('SELECT association_name FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $vars = [
      'association_name' => $assoc ? $assoc['association_name'] : 'Associazione AP',
      'receipt_number' => $pay['receipt_number'],
      'receipt_year' => (string)$pay['receipt_year'],
      'date' => $pay['payment_date'],
      'member_name' => $pay['first_name'].' '.$pay['last_name'],
      'member_email' => $pay['email'] ?? '',
      'year' => (string)$pay['receipt_year'],
      'method' => $pay['method'],
      'amount' => number_format((float)$pay['amount'], 2, ',', '.'),
      'notes' => $pay['notes'] ?? '',
    ];
    $tpl = dirname(__DIR__) . '/templates/documents/receipt.html';
    $html = DocumentService::renderTemplate($tpl, $vars);
    $paths = DocumentService::saveReceipt((int)$pay['receipt_year'], $pay['receipt_number'], $html);
    $docPathPublic = $paths['pdf']
      ? 'storage/documents/receipts/'.$pay['receipt_year'].'/receipt_'.$pay['receipt_number'].'.pdf'
      : 'storage/documents/receipts/'.$pay['receipt_year'].'/receipt_'.$pay['receipt_number'].'.html';
    $docModel = new Document();
    $doc = $docModel->findReceiptByYearNumber((int)$pay['receipt_year'], $pay['receipt_number']);
    if ($doc) {
      // Aggiorna solo il percorso se cambia tipo
      // Sempli e chiaro: inseriamo sempre un nuovo record per semplicità didattica
    }
    $docId = $docModel->create((int)$pay['member_id'], 'receipt', (int)$pay['receipt_year'], $docPathPublic);
    Helpers::addFlash('success', 'Ricevuta rigenerata');
    Helpers::redirect('/documents/'.$docId.'/download');
  }
}
