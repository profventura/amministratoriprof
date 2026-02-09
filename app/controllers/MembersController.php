<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Helpers;
use App\Models\Member;
class MembersController {
  public function index() {
    Auth::require();
    $m = new Member();
    // Recupera tutti i soci senza filtri lato DB (la filtrazione la farà DataTables se non è server-side, 
    // ma qui manteniamo il supporto per i filtri PHP se l'utente li usa nell'URL)
    $filters = [
      'status' => trim($_GET['status'] ?? ''),
      'q' => trim($_GET['q'] ?? '')
    ];
    
    // Per popolare le "card" riassuntive
    $pdo = \App\Core\DB::conn();
    $totalMembers = $pdo->query("SELECT COUNT(*) c FROM members WHERE deleted_at IS NULL")->fetch()['c'];
    $activeMembers = $pdo->query("SELECT COUNT(*) c FROM members WHERE deleted_at IS NULL AND status='active'")->fetch()['c'];
    $newMembersYear = $pdo->query("SELECT COUNT(*) c FROM members WHERE deleted_at IS NULL AND YEAR(registration_date) = " . date('Y'))->fetch()['c'];
    
    $stats = [
        'total' => $totalMembers,
        'active' => $activeMembers,
        'new_this_year' => $newMembersYear
    ];
    
    $rows = $m->all($filters);
    Helpers::view('members/index', [
        'title'=>'Soci',
        'rows'=>$rows,
        'filters'=>$filters,
        'stats' => $stats
    ]);
  }
  public function createForm() {
    Auth::require();
    Helpers::view('members/create', ['title'=>'Nuovo Socio']);
  }
  public function store() {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    
    // Raccogliamo tutti i nuovi campi
    $data = [
      'member_number' => trim($_POST['member_number'] ?? ''),
      'first_name' => trim($_POST['first_name'] ?? ''),
      'last_name' => trim($_POST['last_name'] ?? ''),
      'studio_name' => trim($_POST['studio_name'] ?? ''),
      'email' => trim($_POST['email'] ?? ''),
      'phone' => trim($_POST['phone'] ?? ''),
      'mobile_phone' => trim($_POST['mobile_phone'] ?? ''),
      'address' => trim($_POST['address'] ?? ''),
      'city' => trim($_POST['city'] ?? ''),
      'province' => trim($_POST['province'] ?? ''),
      'zip_code' => trim($_POST['zip_code'] ?? ''),
      'birth_date' => $_POST['birth_date'] ?: null, // Assicura NULL se stringa vuota
      'tax_code' => trim($_POST['tax_code'] ?? ''),
      'billing_cf_piva' => trim($_POST['billing_cf_piva'] ?? ''),
      'is_revisor' => isset($_POST['is_revisor']) ? 1 : 0,
      'revision_number' => trim($_POST['revision_number'] ?? ''),
      'status' => trim($_POST['status'] ?? 'active'),
      'registration_date' => $_POST['registration_date'] ?: null
    ];
    
    if ($data['first_name'] === '' || $data['last_name'] === '') {
      Helpers::addFlash('danger', 'Nome e Cognome sono obbligatori');
      Helpers::redirect('/members/create');
      return;
    }
    
    $m = new Member();
    $id = $m->create($data);
    Helpers::addFlash('success', 'Socio creato correttamente');
    Helpers::redirect('/members/'.$id);
  }
  
  public function editForm($id) {
    Auth::require();
    $m = new Member();
    $row = $m->find((int)$id);
    if (!$row) { http_response_code(404); echo 'Socio non trovato'; return; }
    Helpers::view('members/edit', ['title'=>'Modifica Socio','row'=>$row]);
  }
  public function update($id) {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    
    // Raccogliamo tutti i nuovi campi anche per l'update
    $data = [
      'member_number' => trim($_POST['member_number'] ?? ''),
      'first_name' => trim($_POST['first_name'] ?? ''),
      'last_name' => trim($_POST['last_name'] ?? ''),
      'studio_name' => trim($_POST['studio_name'] ?? ''),
      'email' => trim($_POST['email'] ?? ''),
      'phone' => trim($_POST['phone'] ?? ''),
      'mobile_phone' => trim($_POST['mobile_phone'] ?? ''),
      'address' => trim($_POST['address'] ?? ''),
      'city' => trim($_POST['city'] ?? ''),
      'province' => trim($_POST['province'] ?? ''),
      'zip_code' => trim($_POST['zip_code'] ?? ''),
      'birth_date' => $_POST['birth_date'] ?: null,
      'tax_code' => trim($_POST['tax_code'] ?? ''),
      'billing_cf_piva' => trim($_POST['billing_cf_piva'] ?? ''),
      'is_revisor' => isset($_POST['is_revisor']) ? 1 : 0,
      'revision_number' => trim($_POST['revision_number'] ?? ''),
      'status' => trim($_POST['status'] ?? 'active'),
      'registration_date' => $_POST['registration_date'] ?: null
    ];
    
    $m = new Member();
    $m->update((int)$id, $data);
    Helpers::addFlash('success', 'Dati del socio aggiornati');
    Helpers::redirect('/members/'.$id);
  }
  
  public function delete($id) {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    $m = new Member();
    $m->softDelete((int)$id);
    Helpers::addFlash('success', 'Socio disattivato');
    Helpers::redirect('/members');
  }
  public function show($id) {
    Auth::require();
    $m = new Member();
    $row = $m->find((int)$id);
    if (!$row) { http_response_code(404); echo 'Socio non trovato'; return; }
    
    // Recupera informazioni aggiuntive:
    $pdo = \App\Core\DB::conn();
    
    // 1. Iscrizioni e pagamenti (Storia rinnovo)
    // Usiamo una query che unisce memberships e payments per mostrare Rinnovo, Data, Importo
    $renewals = $pdo->query("
        SELECT m.year, m.status, m.renewal_date, p.payment_date, p.amount
        FROM memberships m
        LEFT JOIN payments p ON p.membership_id = m.id
        WHERE m.member_id = {$row['id']}
        ORDER BY m.year DESC
    ")->fetchAll();
    
    // 2. Corsi frequentati e attestati
    // Mostra titolo corso, data, se ha partecipato e se ha attestato
    $courses = $pdo->query("
        SELECT c.title, c.course_date, cp.certificate_document_id, d.file_path as certificate_path
        FROM course_participants cp
        JOIN courses c ON c.id = cp.course_id
        LEFT JOIN documents d ON d.id = cp.certificate_document_id
        WHERE cp.member_id = {$row['id']}
        ORDER BY c.course_date DESC
    ")->fetchAll();
    
    Helpers::view('members/show', [
        'title'=>'Scheda Socio',
        'row'=>$row,
        'renewals' => $renewals,
        'courses' => $courses
    ]);
  }
}
