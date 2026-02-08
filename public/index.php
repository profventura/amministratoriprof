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
  // Gestione case-sensitive per Linux
  $parts = explode('\\', substr($class, strlen($prefix)));
  // Le cartelle principali in app/ sono spesso lowercase o Capitalized in modo inconsistente
  // Mappiamo le cartelle principali note per forzare il casing corretto se necessario
  // Ma la soluzione migliore è rinominare le cartelle su FS.
  // Qui assumiamo che l'utente rinominerà le cartelle come:
  // app/Core, app/Controllers, app/Models, app/Services
  $path = __DIR__ . '/../app/' . implode('/', $parts) . '.php';
  if (file_exists($path)) require $path;
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
$router = new Router();
$pdoBootstrap = DB::conn();
// Ensure superuser admin exists in users
$adminExists = (int)$pdoBootstrap->query("SELECT COUNT(*) c FROM users WHERE username='admin'")->fetch()['c'];
if ($adminExists === 0) {
  $stmt = $pdoBootstrap->prepare('INSERT INTO users (username,password_hash,role,active) VALUES (?,?,?,?)');
  $stmt->execute(['admin',password_hash('password', PASSWORD_DEFAULT),'admin',1]);
}
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
$router->get('/members/create', [MembersController::class,'createForm']);
$router->post('/members', [MembersController::class,'store']);
$router->get('/members/{id}', [MembersController::class,'show']);
$router->get('/members/{id}/edit', [MembersController::class,'editForm']);
$router->post('/members/{id}/update', [MembersController::class,'update']);
$router->post('/members/{id}/delete', [MembersController::class,'delete']);
// Iscrizioni annuali
$router->get('/memberships', [MembershipsController::class,'index']);
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
$router->get('/documents/download', [DocumentsController::class,'downloadByPath']);
$router->get('/settings', [SettingsController::class,'index']);
$router->post('/settings/update-template', [SettingsController::class,'updateTemplate']);
$router->post('/settings/test-docx', [SettingsController::class,'testDocx']);
$router->post('/settings/update-stamp', [SettingsController::class,'updateStamp']);
$router->post('/settings/preview-stamp', [SettingsController::class,'previewStamp']);
$router->get('/settings/pdf-geometry', [SettingsController::class,'getPdfGeometry']);
$router->dispatch();
