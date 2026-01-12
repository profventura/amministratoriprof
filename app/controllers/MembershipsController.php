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
}
