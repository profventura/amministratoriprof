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
    $filters = [
      'status' => trim($_GET['status'] ?? ''),
      'q' => trim($_GET['q'] ?? '')
    ];
    $rows = $m->all($filters);
    Helpers::view('members/index', ['title'=>'Soci','rows'=>$rows,'filters'=>$filters]);
  }
  public function createForm() {
    Auth::require();
    Helpers::view('members/create', ['title'=>'Nuovo Socio']);
  }
  public function store() {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    $data = [
      'first_name' => trim($_POST['first_name'] ?? ''),
      'last_name' => trim($_POST['last_name'] ?? ''),
      'email' => trim($_POST['email'] ?? ''),
      'phone' => trim($_POST['phone'] ?? ''),
      'address' => trim($_POST['address'] ?? ''),
      'city' => trim($_POST['city'] ?? ''),
      'birth_date' => $_POST['birth_date'] ?? null,
      'tax_code' => trim($_POST['tax_code'] ?? ''),
      'status' => trim($_POST['status'] ?? 'active')
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
    $data = [
      'first_name' => trim($_POST['first_name'] ?? ''),
      'last_name' => trim($_POST['last_name'] ?? ''),
      'email' => trim($_POST['email'] ?? ''),
      'phone' => trim($_POST['phone'] ?? ''),
      'address' => trim($_POST['address'] ?? ''),
      'city' => trim($_POST['city'] ?? ''),
      'birth_date' => $_POST['birth_date'] ?? null,
      'tax_code' => trim($_POST['tax_code'] ?? ''),
      'status' => trim($_POST['status'] ?? 'active')
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
    Helpers::view('members/show', ['title'=>'Scheda Socio','row'=>$row]);
  }
}
