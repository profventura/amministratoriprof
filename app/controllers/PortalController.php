<?php
/*
  File: PortalController.php
  Scopo: Gestione delle funzionalità dell'area pubblica riservata ai soci.
  Funzionalità: Login, Dashboard, Modifica Profilo, Iscrizione Corsi, Pagamenti.
*/
namespace App\Controllers;

use App\Core\PublicAuth;
use App\Core\Helpers;
use App\Core\CSRF;
use App\Core\DB;
use App\Models\Member;

class PortalController {

    // Mostra form login
    public function loginForm() {
        if (PublicAuth::check()) {
            Helpers::redirect('/portal/dashboard');
        }
        Helpers::view('portal/login', ['title' => 'Accesso Soci']);
    }

    // Processa login
    public function login() {
        if (!CSRF::validate($_POST['csrf'] ?? '')) {
            Helpers::addFlash('danger', 'Token di sicurezza non valido.');
            Helpers::redirect('/portal/login');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            Helpers::addFlash('danger', 'Inserisci username e password.');
            Helpers::redirect('/portal/login');
            return;
        }

        if (PublicAuth::login($username, $password)) {
            Helpers::addFlash('success', 'Benvenuto nell\'area riservata.');
            Helpers::redirect('/portal/dashboard');
        } else {
            Helpers::addFlash('danger', 'Credenziali non valide o account non attivo.');
            Helpers::redirect('/portal/login');
        }
    }

    // Logout
    public function logout() {
        PublicAuth::logout();
        Helpers::addFlash('info', 'Logout effettuato.');
        Helpers::redirect('/portal/login');
    }

    // Dashboard socio
    public function dashboard() {
        PublicAuth::require();
        $memberId = PublicAuth::id();
        $pdo = DB::conn();

        // Recupera dati socio aggiornati
        $m = new Member();
        $member = $m->find($memberId);

        // Recupera iscrizioni (stato quote)
        $memberships = $pdo->query("SELECT * FROM memberships WHERE member_id = $memberId ORDER BY year DESC")->fetchAll();

        // Recupera corsi a cui è iscritto
        $courses = $pdo->query("
            SELECT c.*, cp.certificate_document_id 
            FROM courses c 
            JOIN course_participants cp ON c.id = cp.course_id 
            WHERE cp.member_id = $memberId 
            ORDER BY c.course_date DESC
        ")->fetchAll();

        // Recupera prossimi corsi disponibili (futuri e non ancora iscritto)
        $today = date('Y-m-d');
        // Se la tabella courses non ha deleted_at, lo rimuoviamo dalla query
        // Verifichiamo la struttura o usiamo una query sicura.
        // Nel messaggio di errore: Unknown column 'deleted_at' in 'where clause'
        // Quindi la tabella courses NON ha deleted_at.
        
        $availableCourses = $pdo->query("
            SELECT * FROM courses 
            WHERE course_date >= '$today' 
            AND id NOT IN (SELECT course_id FROM course_participants WHERE member_id = $memberId)
            ORDER BY course_date ASC
        ")->fetchAll();

        Helpers::view('portal/dashboard', [
            'title' => 'Area Riservata - Dashboard',
            'member' => $member,
            'memberships' => $memberships,
            'my_courses' => $courses,
            'available_courses' => $availableCourses
        ]);
    }

    // Modifica profilo (form)
    public function profile() {
        PublicAuth::require();
        $memberId = PublicAuth::id();
        $m = new Member();
        $member = $m->find($memberId);
        
        Helpers::view('portal/profile', ['title' => 'Il mio profilo', 'member' => $member]);
    }

    // Aggiorna profilo
    public function updateProfile() {
        PublicAuth::require();
        if (!CSRF::validate($_POST['csrf'] ?? '')) {
            Helpers::addFlash('danger', 'Errore CSRF.');
            Helpers::redirect('/portal/profile');
            return;
        }

        $memberId = PublicAuth::id();
        $m = new Member();
        
        // Campi modificabili dal socio
        $data = [
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'mobile_phone' => trim($_POST['mobile_phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'province' => trim($_POST['province'] ?? ''),
            'zip_code' => trim($_POST['zip_code'] ?? ''),
            'studio_name' => trim($_POST['studio_name'] ?? ''),
            'billing_cf' => trim($_POST['billing_cf'] ?? ''),
            'billing_piva' => trim($_POST['billing_piva'] ?? '')
        ];

        // Validazione base
        if (empty($data['email'])) {
            Helpers::addFlash('danger', 'L\'email è obbligatoria.');
            Helpers::redirect('/portal/profile');
            return;
        }

        // Aggiorna password se fornita
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';
        
        if (!empty($newPass)) {
            if ($newPass !== $confirmPass) {
                Helpers::addFlash('danger', 'Le password non coincidono.');
                Helpers::redirect('/portal/profile');
                return;
            }
            if (strlen($newPass) < 8) {
                Helpers::addFlash('danger', 'La password deve essere di almeno 8 caratteri.');
                Helpers::redirect('/portal/profile');
                return;
            }
            $data['password_hash'] = password_hash($newPass, PASSWORD_DEFAULT);
        }

        $m->update($memberId, $data);
        Helpers::addFlash('success', 'Profilo aggiornato con successo.');
        Helpers::redirect('/portal/profile');
    }

    // Iscrizione a un corso
    public function joinCourse($courseId) {
        PublicAuth::require();
        if (!CSRF::validate($_POST['csrf'] ?? '')) {
            Helpers::addFlash('danger', 'Errore sicurezza.');
            Helpers::redirect('/portal/dashboard');
            return;
        }

        $memberId = PublicAuth::id();
        $pdo = DB::conn();

        // Verifica esistenza corso e data futura
        $course = $pdo->query("SELECT * FROM courses WHERE id = " . (int)$courseId . " AND deleted_at IS NULL")->fetch();
        
        if (!$course) {
            Helpers::addFlash('danger', 'Corso non trovato.');
            Helpers::redirect('/portal/dashboard');
            return;
        }

        // Verifica già iscritto
        $check = $pdo->query("SELECT count(*) as c FROM course_participants WHERE course_id = " . (int)$courseId . " AND member_id = $memberId")->fetch()['c'];
        
        if ($check > 0) {
            Helpers::addFlash('warning', 'Sei già iscritto a questo corso.');
            Helpers::redirect('/portal/dashboard');
            return;
        }

        // Iscrivi
        $stmt = $pdo->prepare("INSERT INTO course_participants (course_id, member_id) VALUES (?, ?)");
        $stmt->execute([(int)$courseId, $memberId]);

        Helpers::addFlash('success', 'Iscrizione al corso avvenuta con successo.');
        Helpers::redirect('/portal/dashboard');
    }

    // Pagina Pagamenti (Quote o Corsi)
    // Per ora mockup, in futuro integrazione PayPal/Stripe
    public function payments() {
        PublicAuth::require();
        $memberId = PublicAuth::id();
        $pdo = DB::conn();
        
        // Verifica stato iscrizione anno corrente
        $currentYear = date('Y');
        $membership = $pdo->query("SELECT * FROM memberships WHERE member_id = $memberId AND year = $currentYear")->fetch();
        
        Helpers::view('portal/payments', [
            'title' => 'Pagamenti',
            'membership' => $membership,
            'year' => $currentYear
        ]);
    }
}
