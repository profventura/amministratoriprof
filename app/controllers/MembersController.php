<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Helpers;
use App\Models\Member;
use App\Controllers\CertificatesController;

class MembersController {
  public function bulkAction() {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    
    $action = $_POST['action'] ?? '';
    $ids = $_POST['selected_ids'] ?? [];
    
    if (empty($ids)) {
        Helpers::addFlash('warning', 'Seleziona almeno un socio');
        Helpers::redirect('/members');
        return;
    }
    
    switch ($action) {
        case 'generate_certificate':
            // Delega al CertificatesController
            // Poiché generateSelected legge da $_POST, possiamo istanziarlo e chiamarlo.
            // Attenzione: generateSelected fa Auth::require() e redirect finale.
            (new CertificatesController())->generateSelected();
            break;
            
        case 'generate_attestato':
            // Placeholder per il futuro
            Helpers::addFlash('info', 'Funzionalità Attestato non ancora implementata per selezione multipla.');
            Helpers::redirect('/members');
            break;

        case 'delete':
            $m = new Member();
            $count = 0;
            foreach ($ids as $id) {
                $m->softDelete((int)$id);
                $count++;
            }
            Helpers::addFlash('success', "$count soci eliminati.");
            Helpers::redirect('/members');
            break;
            
        default:
            Helpers::addFlash('warning', 'Azione non valida');
            Helpers::redirect('/members');
            break;
    }
  }

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
    // FIX: registration_date potrebbe non esistere ancora nel DB remoto se non è stata eseguita la migrazione.
    // Usiamo created_at come fallback se registration_date non esiste, oppure gestiamo l'errore.
    // O meglio, assicuriamoci che la colonna esista prima di interrogarla, o usiamo try-catch.
    
    // Per evitare l'errore bloccante in produzione se manca la colonna, verifichiamo se esiste la colonna
    // O più semplicemente, avvolgiamo in try-catch e in caso di errore usiamo created_at o 0.
    
    try {
        $newMembersYear = $pdo->query("SELECT COUNT(*) c FROM members WHERE deleted_at IS NULL AND YEAR(registration_date) = " . date('Y'))->fetch()['c'];
    } catch (\PDOException $e) {
        // Se registration_date non esiste (es. errore 42S22), fallback su created_at
        if (strpos($e->getMessage(), 'Unknown column') !== false) {
             $newMembersYear = $pdo->query("SELECT COUNT(*) c FROM members WHERE deleted_at IS NULL AND YEAR(created_at) = " . date('Y'))->fetch()['c'];
        } else {
            throw $e;
        }
    }
    
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
      'username' => trim($_POST['username'] ?? ''),
      'phone' => trim($_POST['phone'] ?? ''),
      'mobile_phone' => trim($_POST['mobile_phone'] ?? ''),
      'address' => trim($_POST['address'] ?? ''),
      'city' => trim($_POST['city'] ?? ''),
      'province' => trim($_POST['province'] ?? ''),
      'zip_code' => trim($_POST['zip_code'] ?? ''),
      'birth_date' => $_POST['birth_date'] ?: null, // Assicura NULL se stringa vuota
      'tax_code' => trim($_POST['tax_code'] ?? ''),
      'billing_cf' => trim($_POST['billing_cf'] ?? ''),
      'billing_piva' => trim($_POST['billing_piva'] ?? ''),
      'is_revisor' => isset($_POST['is_revisor']) ? 1 : 0,
      'revision_number' => trim($_POST['revision_number'] ?? ''),
      'status' => trim($_POST['status'] ?? 'active'),
      'registration_date' => $_POST['registration_date'] ?: null
    ];
    
    // Gestione Password
    $password = $_POST['password'] ?? '';
    if (!empty($password)) {
        $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }
    
    if ($data['first_name'] === '' || $data['last_name'] === '') {
      Helpers::addFlash('danger', 'Nome e Cognome sono obbligatori');
      Helpers::redirect('/members/create');
      return;
    }

    // Calcolo automatico Username: n.cognome (solo minuscolo, rimuovi spazi e caratteri speciali)
    $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $data['first_name']));
    $cleanSurname = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $data['last_name']));
    $baseUsername = substr($cleanName, 0, 1) . '.' . $cleanSurname;
    
    // Gestiamo unicità username
    $username = $baseUsername;
    $counter = 1;
    $pdo = \App\Core\DB::conn();
    while (true) {
        $exists = $pdo->prepare("SELECT id FROM members WHERE username = ? AND deleted_at IS NULL");
        $exists->execute([$username]);
        if (!$exists->fetch()) break;
        $username = $baseUsername . $counter;
        $counter++;
    }
    $data['username'] = $username;

    $m = new Member();
    // Check username duplicato (già gestito sopra ma lasciamo per sicurezza)
    if (!empty($data['username'])) {
        $exists = $pdo->prepare("SELECT id FROM members WHERE username = ? AND deleted_at IS NULL");
        $exists->execute([$data['username']]);
        if ($exists->fetch()) {
             // Non dovrebbe accadere col while sopra
             Helpers::addFlash('danger', 'Errore generazione username');
             Helpers::redirect('/members/create');
             return;
        }
    }
    
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
      // Username NON aggiornabile da post, rimane quello esistente o viene generato se mancante (legacy)
      'phone' => trim($_POST['phone'] ?? ''),
      'mobile_phone' => trim($_POST['mobile_phone'] ?? ''),
      'address' => trim($_POST['address'] ?? ''),
      'city' => trim($_POST['city'] ?? ''),
      'province' => trim($_POST['province'] ?? ''),
      'zip_code' => trim($_POST['zip_code'] ?? ''),
      'birth_date' => $_POST['birth_date'] ?: null,
      'tax_code' => trim($_POST['tax_code'] ?? ''),
      'billing_cf' => trim($_POST['billing_cf'] ?? ''),
      'billing_piva' => trim($_POST['billing_piva'] ?? ''),
      'is_revisor' => isset($_POST['is_revisor']) ? 1 : 0,
      'revision_number' => trim($_POST['revision_number'] ?? ''),
      'status' => trim($_POST['status'] ?? 'active'),
      'registration_date' => $_POST['registration_date'] ?: null
    ];
    
    // Recupera utente attuale per preservare username se non settato o per evitare modifiche
    $pdo = \App\Core\DB::conn();
    $current = $pdo->query("SELECT username FROM members WHERE id=".(int)$id)->fetch();
    
    if (empty($current['username'])) {
         // Genera se manca
         $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $data['first_name']));
         $cleanSurname = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $data['last_name']));
         $baseUsername = substr($cleanName, 0, 1) . '.' . $cleanSurname;
         $username = $baseUsername;
         $counter = 1;
         while (true) {
            $exists = $pdo->prepare("SELECT id FROM members WHERE username = ? AND id <> ? AND deleted_at IS NULL");
            $exists->execute([$username, $id]);
            if (!$exists->fetch()) break;
            $username = $baseUsername . $counter;
            $counter++;
         }
         $data['username'] = $username;
    } else {
        // Mantieni esistente
        $data['username'] = $current['username'];
    }

    // Gestione Password in Update
    $password = $_POST['password'] ?? '';
    if (!empty($password)) {
        $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }
    
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
