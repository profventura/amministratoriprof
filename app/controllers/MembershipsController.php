<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\Helpers;
use App\Models\Membership;
class MembershipsController {
  public function index() {
    Auth::require();
    $year = (int)($_GET['year'] ?? date('Y'));
    $rows = (new Membership())->byYear($year);
    Helpers::view('memberships/index', ['title'=>'Iscrizioni '.$year,'rows'=>$rows,'year'=>$year]);
  }

  public function edit($id) {
    Auth::require();
    $m = new Membership();
    $row = $m->findWithMember((int)$id);
    if (!$row) { http_response_code(404); echo 'Iscrizione non trovata'; return; }
    Helpers::view('memberships/edit', ['title'=>'Modifica Iscrizione', 'row'=>$row]);
  }

  public function update($id) {
    Auth::require();
    if (!Helpers::validateCSRF()) { return; }
    
    $data = [
      'status' => $_POST['status'] ?? 'pending',
      'renewal_date' => $_POST['renewal_date'] ?? null
    ];
    
    (new Membership())->update((int)$id, $data);
    Helpers::addFlash('success', 'Iscrizione aggiornata');
    
    // Redirect all'anno corretto
    $m = (new Membership())->find((int)$id);
    Helpers::redirect('/memberships?year=' . $m['year']);
  }

  public function delete($id) {
    Auth::require();
    $m = new Membership();
    $row = $m->find((int)$id);
    if ($row) {
        $m->delete((int)$id);
        Helpers::addFlash('success', 'Iscrizione eliminata');
        Helpers::redirect('/memberships?year=' . $row['year']);
    } else {
        Helpers::redirect('/memberships');
    }
  }
}
