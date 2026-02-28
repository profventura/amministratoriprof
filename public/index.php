<?php
// declare(strict_types=1); // Disabilitato temporaneamente per compatibilità
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// Autoload vendor libraries if present (Dompdf, PHPMailer, etc.)
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorAutoload)) { require $vendorAutoload; }
spl_autoload_register(function($class){
  $prefix = 'App\\';
  if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
  // Gestione case-sensitive per Linux: normalizza tutto in lowercase per la ricerca del file
  // Questo assume che l'utente abbia rinominato le cartelle in lowercase (controllers, models, core)
  // OPPURE che l'utente abbia rinominato correttamente in CamelCase.
  
  // Tentativo 1: Path standard (CamelCase come nel namespace)
  $parts = explode('\\', substr($class, strlen($prefix)));
  $path = __DIR__ . '/../app/' . implode('/', $parts) . '.php';
  
  if (file_exists($path)) {
      require $path;
      return;
  }
  
  // Tentativo 2: Path con cartelle in lowercase (comune errore su FTP)
  // App\Core\Router -> app/core/Router.php
  $lcParts = $parts;
  // Convertiamo solo la prima parte (es. Core -> core) in lowercase, lasciando il file (Router.php) CaseSensitive
  if (count($lcParts) > 1) {
      $lcParts[0] = strtolower($lcParts[0]); 
  }
  $pathLc = __DIR__ . '/../app/' . implode('/', $lcParts) . '.php';
  
  if (file_exists($pathLc)) {
      require $pathLc;
      return;
  }
});

use App\Core\Router;
use App\Core\Auth;
use App\Core\Helpers;
use App\Core\DB;
use App\Controllers\AuthController;
use App\Controllers\CertificatesController;
use App\Controllers\CoursesController;
use App\Controllers\DMCertificatesController;
use App\Controllers\ReceiptsController;
use App\Controllers\SettingsController;
use App\Controllers\MembersController;
use App\Controllers\MembershipsController;
use App\Controllers\APPaymentsController;
use App\Controllers\DocumentsController;
use App\Controllers\PortalController;

$router = new Router();
$pdoBootstrap = DB::conn();
// Ensure superuser admin exists in users
$adminExists = (int)$pdoBootstrap->query("SELECT COUNT(*) c FROM users WHERE username='admin'")->fetch()['c'];
if ($adminExists === 0) {
  $stmt = $pdoBootstrap->prepare('INSERT INTO users (username,password_hash,role,active) VALUES (?,?,?,?)');
  $stmt->execute(['admin',password_hash('password', PASSWORD_DEFAULT),'admin',1]);
}

// Rotte Area Soci (Portal)
$router->get('/portal/login', [PortalController::class, 'loginForm']);
$router->post('/portal/login', [PortalController::class, 'login']);
$router->get('/portal/logout', [PortalController::class, 'logout']);
$router->get('/portal/dashboard', [PortalController::class, 'dashboard']);
$router->get('/portal/profile', [PortalController::class, 'profile']);
$router->post('/portal/profile', [PortalController::class, 'updateProfile']);
$router->get('/portal/payments', [PortalController::class, 'payments']);
$router->post('/portal/courses/{id}/join', [PortalController::class, 'joinCourse']);

// Rotte Admin
$router->get('/login', [AuthController::class,'loginForm']);
$router->post('/login', [AuthController::class,'login']);
$router->get('/logout', [AuthController::class,'logout']);
$router->get('/', function(){
  if (!Auth::check()) { Helpers::redirect('/login'); }
  $pdo = DB::conn();
  $year = (int)date('Y');
  $counts = [
    'members_total' => (int)$pdo->query('SELECT COUNT(*) c FROM members WHERE deleted_at IS NULL')->fetch()['c'],
    'regular_current_year' => (int)$pdo->query('SELECT COUNT(*) c FROM memberships WHERE year='.$year.' AND status=\'regular\'')->fetch()['c'],
    'not_regular_current_year' => (int)$pdo->query('SELECT COUNT(*) c FROM memberships WHERE year='.$year.' AND status<>\'regular\'')->fetch()['c'],
    'payments_current_year' => (int)$pdo->query('SELECT COUNT(*) c FROM payments WHERE YEAR(payment_date)='.$year)->fetch()['c'],
    'documents_current_year' => (int)$pdo->query('SELECT COUNT(*) c FROM documents WHERE year='.$year)->fetch()['c'],
  ];
  $income = (float)$pdo->query('SELECT COALESCE(SUM(amount),0) s FROM cash_flows WHERE type=\'income\' AND YEAR(flow_date)='.$year)->fetch()['s'];
  $expense = (float)$pdo->query('SELECT COALESCE(SUM(amount),0) s FROM cash_flows WHERE type=\'expense\' AND YEAR(flow_date)='.$year)->fetch()['s'];
  $counts['cash_income'] = $income;
  $counts['cash_expense'] = $expense;
  $counts['cash_balance'] = $income - $expense;
  Helpers::view('dashboard', ['title'=>'Dashboard','counts'=>$counts]);
});
// Aggiunta rotta diagnostica
$router->get('/check_env.php', function(){
    require __DIR__ . '/../check_env.php';
});
$router->get('/admin/migrate', function(){
  if (!Auth::isAdmin()) { http_response_code(403); echo '403'; return; }
  $pdo = DB::conn();
  $base = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR;
  $files = [$base . 'schema.sql', $base . 'seed.sql'];
  foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $sql = file_get_contents($file);
    $chunks = array_filter(array_map('trim', preg_split('/;\\s*/', $sql)));
    foreach ($chunks as $chunk) { if ($chunk === '') continue; try { $pdo->exec($chunk); } catch (\Throwable $e) {} }
  }
  \App\Core\Helpers::addFlash('success', 'Schema e dati di esempio importati');
  \App\Core\Helpers::redirect('/');
});
// Soci (members)
$router->get('/members', [MembersController::class,'index']);
$router->post('/members/bulk-action', [MembersController::class,'bulkAction']);
$router->get('/members/create', [MembersController::class,'createForm']);
$router->post('/members', [MembersController::class,'store']);
$router->get('/members/{id}', [MembersController::class,'show']);
$router->get('/members/{id}/edit', [MembersController::class,'editForm']);
$router->post('/members/{id}/update', [MembersController::class,'update']);
$router->post('/members/{id}/delete', [MembersController::class,'delete']);
// Iscrizioni annuali
$router->get('/memberships', [MembershipsController::class,'index']);
$router->post('/memberships/bulk-action', [MembershipsController::class,'bulkAction']);
$router->get('/memberships/{id}/edit', [MembershipsController::class,'edit']);
$router->post('/memberships/{id}/edit', [MembershipsController::class,'update']);
$router->post('/memberships/{id}/delete', [MembershipsController::class,'delete']);

// Pagamenti AP (quote)
$router->get('/ap/payments/create', [APPaymentsController::class,'createForm']);
$router->post('/ap/payments', [APPaymentsController::class,'store']);
$router->get('/documents/{id}/download', [DocumentsController::class,'download']);
$router->post('/documents/membership-certificate/generate', [CertificatesController::class,'generateMember']);
$router->post('/documents/membership-certificate/generate-mass', [CertificatesController::class,'generateMass']);
// Ricevute
$router->get('/receipts', [ReceiptsController::class,'index']);
$router->get('/receipts/{id}/download', [ReceiptsController::class,'download']);
$router->post('/receipts/{id}/regenerate', [ReceiptsController::class,'regenerate']);
// Corsi e Attestati DM
$router->get('/courses', [CoursesController::class,'index']);
$router->get('/courses/create', [CoursesController::class,'createForm']);
$router->post('/courses', [CoursesController::class,'store']);
$router->get('/courses/{id}', [CoursesController::class,'show']);
$router->get('/courses/{id}/edit', [CoursesController::class,'editForm']);
$router->post('/courses/{id}/update', [CoursesController::class,'update']);
$router->post('/courses/{id}/delete', [CoursesController::class,'delete']);
$router->post('/courses/{id}/participants/add', [CoursesController::class,'addParticipant']);
$router->post('/courses/{id}/participants/remove', [CoursesController::class,'removeParticipant']);
$router->post('/documents/dm-certificate/{id}/generate', [DMCertificatesController::class,'generateSingle']);
$router->post('/documents/dm-certificate/{id}/generate-mass', [DMCertificatesController::class,'generateMass']);
$router->post('/documents/{id}/delete', [DocumentsController::class,'delete']);
$router->post('/documents/{id}/email', [DocumentsController::class,'sendEmail']);
$router->get('/documents/download', [DocumentsController::class,'downloadByPath']);
$router->get('/settings', [SettingsController::class,'index']);
$router->get('/settings/certificati', [SettingsController::class,'certificati']);
$router->get('/settings/attestati', [SettingsController::class,'attestati']);
$router->get('/settings/ricevute', [SettingsController::class,'ricevute']);
$router->post('/settings/attestati/update-template', [SettingsController::class,'updateAttestatiTemplate']);
$router->post('/settings/attestati/update-stamp', [SettingsController::class,'updateAttestatiStamp']);
$router->post('/settings/attestati/preview-stamp', [SettingsController::class,'previewAttestatiStamp']);
$router->post('/settings/ricevute/update-template', [SettingsController::class,'updateRicevuteTemplate']);
$router->post('/settings/ricevute/update-stamp', [SettingsController::class,'updateRicevuteStamp']);
$router->post('/settings/ricevute/preview-stamp', [SettingsController::class,'previewRicevuteStamp']);
$router->get('/settings/import-export', [SettingsController::class,'importExport']);
$router->get('/settings/export', [SettingsController::class,'export']);
$router->post('/settings/import', [SettingsController::class,'import']);
$router->get('/settings/import/sample', [SettingsController::class,'downloadSampleCsv']);
$router->get('/settings/email', [SettingsController::class,'email']);
$router->post('/settings/email/update', [SettingsController::class,'updateEmailSettings']);
$router->post('/settings/email/test', [SettingsController::class,'testEmail']);
$router->post('/settings/update-public-url', [SettingsController::class,'updatePublicUrl']);
$router->post('/settings/update-template', [SettingsController::class,'updateTemplate']);
$router->post('/settings/test-docx', [SettingsController::class,'testDocx']);
$router->post('/settings/update-stamp', [SettingsController::class,'updateStamp']);
$router->post('/settings/preview-stamp', [SettingsController::class,'previewStamp']);
$router->get('/settings/pdf-geometry', [SettingsController::class,'getPdfGeometry']);
$router->dispatch();
