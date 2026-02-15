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
    if (!Helpers::validateCSRF()) { return; }
    
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

  public function bulkAction() {
      Auth::require();
      if (!Helpers::validateCSRF()) { return; }

      $action = $_POST['action'] ?? '';
      $ids = $_POST['selected_ids'] ?? [];
      $year = (int)($_POST['year'] ?? date('Y'));

      if (empty($ids)) {
          Helpers::addFlash('warning', 'Nessuna iscrizione selezionata.');
          Helpers::redirect('/memberships?year='.$year);
          return;
      }

      switch ($action) {
          case 'generate_certificate':
              // Generazione certificati per gli ID selezionati
              $docController = new DocumentsController();
              $docController->generateMembershipCertificateMassive($year, $ids);
              return; // Il controller gestirà il download o redirect

          case 'delete':
              $m = new Membership();
              $count = 0;
              foreach ($ids as $id) {
                  $m->delete((int)$id);
                  $count++;
              }
              Helpers::addFlash('success', "$count iscrizioni eliminate.");
              Helpers::redirect('/memberships?year='.$year);
              break;
              
          default:
              Helpers::addFlash('warning', 'Azione non riconosciuta.');
              Helpers::redirect('/memberships?year='.$year);
              break;
      }
  }
}
