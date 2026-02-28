<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Helpers;
use App\Models\Course;
use App\Models\CourseParticipant;
use App\Models\Member;
class CoursesController {
  public function index() {
    Auth::require();
    $rows = (new Course())->all();
    Helpers::view('courses/index', ['title'=>'Corsi','rows'=>$rows]);
  }
  public function createForm() {
    Auth::require();
    Helpers::view('courses/form', ['title'=>'Nuovo Corso','course'=>null]);
  }
  public function store() {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    $data = [
      'title'=>trim($_POST['title'] ?? ''),
      'description'=>trim($_POST['description'] ?? ''),
      'course_date'=>$_POST['course_date'] ?? date('Y-m-d'),
      'start_time'=>$_POST['start_time'] ?? null,
      'end_time'=>$_POST['end_time'] ?? null,
      'year'=>(int)($_POST['year'] ?? date('Y')),
    ];
    if ($data['title'] === '') { Helpers::addFlash('danger','Titolo obbligatorio'); Helpers::redirect('/courses/create'); return; }
    $id = (new Course())->create($data);
    Helpers::addFlash('success','Corso creato');
    Helpers::redirect('/courses/'.$id);
  }
  public function editForm($id) {
    Auth::require();
    $course = (new Course())->find((int)$id);
    if (!$course) { http_response_code(404); echo 'Corso non trovato'; return; }
    Helpers::view('courses/form', ['title'=>'Modifica Corso','course'=>$course]);
  }
  public function update($id) {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    $data = [
      'title'=>trim($_POST['title'] ?? ''),
      'description'=>trim($_POST['description'] ?? ''),
      'course_date'=>$_POST['course_date'] ?? date('Y-m-d'),
      'start_time'=>$_POST['start_time'] ?? null,
      'end_time'=>$_POST['end_time'] ?? null,
      'year'=>(int)($_POST['year'] ?? date('Y')),
    ];
    (new Course())->update((int)$id, $data);
    Helpers::addFlash('success','Corso aggiornato');
    Helpers::redirect('/courses/'.$id);
  }
  public function delete($id) {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    (new Course())->delete((int)$id);
    Helpers::addFlash('success','Corso eliminato');
    Helpers::redirect('/courses');
  }
  public function show($id) {
    Auth::require();
    $course = (new Course())->find((int)$id);
    if (!$course) { http_response_code(404); echo 'Corso non trovato'; return; }
    $participants = (new CourseParticipant())->list((int)$id);
    $members = (new Member())->all();
    Helpers::view('courses/show', ['title'=>'Dettaglio Corso','course'=>$course,'participants'=>$participants,'members'=>$members]);
  }
  public function addParticipant($id) {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    
    // Supporto per aggiunta singola o massiva
    if (isset($_POST['member_ids']) && is_array($_POST['member_ids'])) {
        // Massiva
        $count = 0;
        foreach ($_POST['member_ids'] as $mid) {
            $mid = (int)$mid;
            if ($mid > 0) {
                (new CourseParticipant())->add((int)$id, $mid);
                $count++;
            }
        }
        if ($count > 0) Helpers::addFlash('success', "$count partecipanti aggiunti");
        else Helpers::addFlash('warning', "Nessun partecipante selezionato");
        
    } else {
        // Singola (legacy)
        $memberId = (int)($_POST['member_id'] ?? 0);
        if ($memberId <= 0) { Helpers::addFlash('danger','Seleziona un socio'); Helpers::redirect('/courses/'.$id); return; }
        (new CourseParticipant())->add((int)$id, $memberId);
        Helpers::addFlash('success','Partecipante aggiunto');
    }
    
    Helpers::redirect('/courses/'.$id);
  }
  public function removeParticipant($id) {
    Auth::require();
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Token CSRF non valido'; return; }
    $memberId = (int)($_POST['member_id'] ?? 0);
    (new CourseParticipant())->remove((int)$id, $memberId);
    Helpers::addFlash('success','Partecipante rimosso');
    Helpers::redirect('/courses/'.$id);
  }
}
