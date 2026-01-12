<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Helpers;
use App\Core\DB;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Document;
use App\Models\CashFlow;
use App\Services\DocumentService;
class APPaymentsController {
  public function createForm() {
    Auth::require();
    $members = (new Member())->all();
    $year = (int)date('Y');
    Helpers::view('ap_payments/create', ['title'=>'Nuovo Pagamento','members'=>$members,'year'=>$year]);
  }
  public function store() {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    $memberId = (int)($_POST['member_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $method = trim($_POST['method'] ?? 'bank');
    $notes = trim($_POST['notes'] ?? '');
    $year = (int)($_POST['year'] ?? date('Y'));
    $date = $_POST['payment_date'] ?? date('Y-m-d');
    if ($memberId <= 0 || $amount <= 0) {
      Helpers::addFlash('danger', 'Seleziona un socio e un importo valido');
      Helpers::redirect('/ap/payments/create'); return;
    }
    $pdo = DB::conn();
    $memModel = new Membership();
    $membership = $memModel->getOrCreate($memberId, $year);
    $set = $pdo->query('SELECT * FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $seqYear = $set ? (int)$set['receipt_sequence_year'] : (int)date('Y');
    $seqCur = $set ? (int)$set['receipt_sequence_current'] : 0;
    if ($seqYear !== $year) { $seqCur = 0; $seqYear = $year; }
    $seqCur++;
    $pdo->prepare('UPDATE settings SET receipt_sequence_current=?, receipt_sequence_year=?, updated_at=NOW() WHERE id=?')
      ->execute([$seqCur, $seqYear, $set ? $set['id'] : 1]);
    $number = str_pad((string)$seqCur, 4, '0', STR_PAD_LEFT);
    $payModel = new Payment();
    $paymentId = $payModel->create([
      'member_id'=>$memberId,'membership_id'=>$membership['id'],'payment_date'=>$date,'amount'=>$amount,
      'method'=>$method,'notes'=>$notes,'receipt_number'=>$number,'receipt_year'=>$year
    ]);
    $memModel->setRegular($membership['id']);
    $mbr = (new Member())->find($memberId);
    $assoc = $pdo->query('SELECT association_name FROM settings ORDER BY id DESC LIMIT 1')->fetch();
    $vars = [
      'association_name' => $assoc ? $assoc['association_name'] : 'Associazione AP',
      'receipt_number' => $number,
      'receipt_year' => (string)$year,
      'date' => $date,
      'member_name' => $mbr ? ($mbr['first_name'].' '.$mbr['last_name']) : '',
      'member_email' => $mbr ? ($mbr['email'] ?? '') : '',
      'year' => (string)$year,
      'method' => $method,
      'amount' => number_format($amount, 2, ',', '.'),
      'notes' => $notes,
    ];
    $tpl = dirname(__DIR__) . '/templates/documents/receipt.html';
    $html = DocumentService::renderTemplate($tpl, $vars);
    $paths = DocumentService::saveReceipt($year, $number, $html);
    $docPathPublic = null;
    if ($paths['pdf']) {
      $docPathPublic = 'storage/documents/receipts/'.$year.'/receipt_'.$number.'.pdf';
    } else {
      $docPathPublic = 'storage/documents/receipts/'.$year.'/receipt_'.$number.'.html';
    }
    (new Document())->create($memberId, 'receipt', $year, $docPathPublic);
    $catId = (int)$pdo->query("SELECT id FROM cash_categories WHERE name='Quote associative' AND type='income' LIMIT 1")->fetch()['id'];
    (new CashFlow())->addIncome($date, $catId, 'Quota annuale '.$vars['member_name'], $amount, $paymentId);
    Helpers::addFlash('success', 'Pagamento registrato e ricevuta generata');
    Helpers::redirect('/members/'.$memberId);
  }
}
