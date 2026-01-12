<?php
namespace App\Controllers;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Helpers;
class AuthController {
  public function loginForm() {
    Helpers::view('login', ['title'=>'Login']);
  }
  public function login() {
    if (!CSRF::validate($_POST['csrf'] ?? '')) { http_response_code(400); echo 'Bad CSRF'; return; }
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $res = Auth::login($username, $password);
    if ($res === true) {
      Helpers::redirect('/');
    } else {
      Helpers::view('login', ['title'=>'Login','error'=>'Credenziali non valide']);
    }
  }
  public function logout() {
    Auth::logout();
    Helpers::redirect('/login');
  }
}

