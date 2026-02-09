<?php
use App\Core\Helpers;
use App\Core\Auth;
$config = require __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<!--
  File: layout.php
  Scopo: Layout principale dell’applicazione (header, sidebar, breadcrumb, inclusione scripts e contenuto).
  Spiegazione: Tutte le viste vengono renderizzate all’interno di questo layout comune.
-->
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" type="image/png" href="<?php echo Helpers::url('public/images/logos/favicon.png'); ?>?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="<?php echo Helpers::url('public/css/fonts-manrope.css'); ?>" />
  <link rel="stylesheet" href="<?php echo Helpers::url('public/css/styles.css'); ?>" />
  <link rel="preload" href="<?php echo Helpers::url('public/fonts/tabler-icons/fonts/tabler-icons.woff2?v2.11.0'); ?>" as="font" type="font/woff2" crossorigin>
  <title><?php echo htmlspecialchars($title ?? 'TechLab PC'); ?></title>
</head>
<body class="link-sidebar">
  <!-- Preloader
  <div class="preloader">
    <img src="<?php //echo Helpers::url('public/images/logos/favicon.png'); ?>" alt="loader" class="lds-ripple img-fluid" />
  </div>
  -->
  <div id="main-wrapper">
    <?php if ($template === 'login') { ?>
      <div class="position-relative overflow-hidden radial-gradient min-vh-100 w-100">
        <div class="position-relative z-index-5">
          <div class="row gx-0">
            <div class="col-lg-6 col-xl-5 col-xxl-4">
              <div class="min-vh-100 bg-body row justify-content-center align-items-center p-5">
                <div class="col-12 auth-card">
                  <a href="<?php echo Helpers::url('/'); ?>" class="text-nowrap logo-img d-block w-100">
                    <img src="<?php echo Helpers::url('public/images/logos/logo_v3_scuro.png'); ?>" width="400" alt="TechLab PC" />
                  </a>
                  <h2 class="mb-2 mt-4 fs-7 fw-bolder">Sign In</h2>
                  <p class="mb-9">Accedi al Gestionale Di Amministratori Professionisti</p>
                  <?php if (isset($error)) { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?>
                  <?php
                  $viewFile = __DIR__ . '/' . $template . '.php';
                  if (file_exists($viewFile)) { require $viewFile; }
                  ?>
                </div>
              </div>
            </div>
            <div class="col-lg-6 col-xl-7 col-xxl-8 position-relative overflow-hidden bg-dark d-none d-lg-block">
              <div class="circle-top"></div>
              <div>
                <img src="<?php echo Helpers::url('public/images/logos/logo.png'); ?>" class="circle-bottom" alt="Logo-Dark" />
              </div>
              <div class="d-lg-flex align-items-center z-index-5 position-relative h-n80">
                <div class="row justify-content-center w-100">
                  <div class="col-lg-6">
                    <h2 class="text-white fs-10 mb-3 lh-sm">
                      Benvenuto al <br />  GestAP
                    </h2>
                    <span class="opacity-75 fs-4 text-white d-block mb-3">
                      Gestionale Amministratori Professionisti 
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php } else { ?>
    <!-- Sidebar Start -->
    <aside class="left-sidebar with-vertical">
      <div>
        <div>
          <div class="brand-logo d-flex align-items-center">
          <a href="<?php echo Helpers::url('/'); ?>" class="text-nowrap logo-img">
            <img src="<?php echo Helpers::url('public/images/logos/logo_v3_scuro.png'); ?>" width="170" alt="TechLab PC" />
          </a>
        </div>
        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
          <ul class="sidebar-menu" id="sidebarnav">
            <li class="sidebar-item">
              <a class="sidebar-link" href="<?php echo Helpers::url('/'); ?>" aria-expanded="false">
                <iconify-icon icon="solar:widget-add-line-duotone"></iconify-icon>
                <span class="hide-menu">Dashboard</span>
              </a>
            </li>
            
            <li class="sidebar-item">
              <a class="sidebar-link" href="<?php echo Helpers::url('/members'); ?>" aria-expanded="false">
                <iconify-icon icon="solar:user-circle-line-duotone"></iconify-icon>
                <span class="hide-menu">Soci</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="<?php echo Helpers::url('/memberships'); ?>" aria-expanded="false">
                <iconify-icon icon="solar:diploma-verified-outline"></iconify-icon>
                <span class="hide-menu">Iscrizioni</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="<?php echo Helpers::url('/ap/payments/create'); ?>" aria-expanded="false">
                <iconify-icon icon="solar:card-line-duotone"></iconify-icon>
                <span class="hide-menu">Pagamenti Quote</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="<?php echo Helpers::url('/courses'); ?>" aria-expanded="false">
                <iconify-icon icon="solar:calendar-line-duotone"></iconify-icon>
                <span class="hide-menu">Corsi</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="<?php echo Helpers::url('/receipts'); ?>" aria-expanded="false">
                <iconify-icon icon="solar:bill-list-linear"></iconify-icon>
                <span class="hide-menu">Ricevute</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                <iconify-icon icon="solar:settings-line-duotone"></iconify-icon>
                <span class="hide-menu">Settings</span>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                  <a href="<?php echo Helpers::url('/settings'); ?>" class="sidebar-link">
                    <span class="icon-small"></span>
                    <span class="hide-menu">Certificati</span>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a href="<?php echo Helpers::url('/settings/attestati'); ?>" class="sidebar-link">
                    <span class="icon-small"></span>
                    <span class="hide-menu">Attestati</span>
                  </a>
                </li>
                <li class="sidebar-item">
                  <a href="<?php echo Helpers::url('/settings/email'); ?>" class="sidebar-link">
                    <span class="icon-small"></span>
                    <span class="hide-menu">Email</span>
                  </a>
                </li>
              </ul>
            </li>
            
             <?php if (Auth::check()) { ?>
            <li class="sidebar-item">
               <a class="sidebar-link" href="<?php echo Helpers::url('/logout'); ?>" aria-expanded="false">
                <iconify-icon icon="solar:logout-3-line-duotone"></iconify-icon>
                <span class="hide-menu">Logout</span>
              </a>
            </li>
            <?php } else { ?>
            <li class="sidebar-item">
               <a class="sidebar-link" href="<?php echo Helpers::url('/login'); ?>" aria-expanded="false">
                <iconify-icon icon="solar:login-3-line-duotone"></iconify-icon>
                <span class="hide-menu">Login</span>
              </a>
            </li>
            <?php } ?>
          </ul>
          </nav>
        </div>
      </div>
    </aside>

    <div class="page-wrapper">
      <header class="topbar">
        <div class="with-vertical">
        <nav class="navbar navbar-expand-lg p-0">
          <ul class="navbar-nav">
            <li class="nav-item nav-icon-hover-bg rounded-circle d-flex">
              <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                <iconify-icon icon="solar:hamburger-menu-line-duotone" class="fs-6"></iconify-icon>
              </a>
            </li>
          </ul>
          <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
              <?php if (Auth::check()) { ?>
              <li class="nav-item dropdown">
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                  <div class="message-body">
                    <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                      <i class="ti ti-user fs-6"></i>
                      <p class="mb-0 fs-3"><?php echo htmlspecialchars(Auth::user()['name']); ?></p>
                    </a>
                    <a href="<?php echo Helpers::url('/logout'); ?>" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
                  </div>
                </div>
              </li>
              <?php } ?>
            </ul>
          </div>
        </nav>
      </header>
      

      <div class="body-wrapper d-flex flex-column" style="min-height: calc(100vh - 70px);">
        <div class="container-fluid flex-grow-1">
          <?php $flashes = \App\Core\Helpers::getFlashes(); ?>
          <?php if (!empty($flashes)) { ?>
          <div class="modal fade" id="flashModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Esito operazione</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <?php foreach ($flashes as $f) { ?>
                  <div class="alert alert-<?php echo $f['type']==='danger'?'danger':'success'; ?> mb-2">
                    <?php echo htmlspecialchars($f['message']); ?>
                  </div>
                  <?php } ?>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
              </div>
            </div>
          </div>
          <?php } ?>
          <?php
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            if ($scriptDir !== '/' && strpos($uri, $scriptDir) === 0) { $uri = substr($uri, strlen($scriptDir)); }
            $uri = rtrim($uri, '/') ?: '/';
            $segments = $uri === '/' ? [] : explode('/', trim($uri, '/'));
            $labels = [
              'members' => 'Soci',
              'memberships' => 'Iscrizioni',
              'ap' => 'Pagamenti Quote',
              'receipts' => 'Ricevute',
              'courses' => 'Corsi',
              'settings' => 'Settings'
            ];
            $seg0 = $segments[0] ?? null;
            $label0 = $seg0 ? ($labels[$seg0] ?? ucfirst(str_replace('-', ' ', $seg0))) : null;
            $finalLabel = null;
            if ($seg0) {
              if (count($segments) >= 2) {
                $seg1 = $segments[1];
                if ($seg1 === 'create') { $finalLabel = 'Nuovo'; }
                elseif ($seg1 === 'edit' || (isset($segments[2]) && $segments[2] === 'edit')) { $finalLabel = 'Modifica'; }
                elseif (is_numeric($seg1)) { $finalLabel = (isset($segments[2]) && $segments[2] === 'edit') ? 'Modifica' : 'Dettaglio'; }
                else { $finalLabel = ucfirst(str_replace('-', ' ', $seg1)); }
              } else {
                $finalLabel = 'Lista';
              }
            } else {
              $finalLabel = !empty($title) ? $title : 'Dashboard';
            }
            if (!empty($title)) { $finalLabel = $title; }
          ?>
          <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb border border-primary px-3 py-2 rounded">
              <li class="breadcrumb-item">
                <a href="<?php echo Helpers::url('/'); ?>" class="text-primary d-flex align-items-center">
                  <iconify-icon icon="solar:home-2-line-duotone" class="fs-4 mt-1"></iconify-icon>
                </a>
              </li>
              <?php if ($seg0) { ?>
              <li class="breadcrumb-item">
                <a href="<?php echo Helpers::url('/' . $seg0); ?>" class="text-primary"><?php echo htmlspecialchars($label0); ?></a>
              </li>
              <?php } ?>
            </ol>
          </nav>
          <?php if (!empty($flashes)) { ?>
          <script>
            document.addEventListener('DOMContentLoaded', function () {
              var m = document.getElementById('flashModal');
              if (m) {
                var inst = new bootstrap.Modal(m);
                m.addEventListener('hide.bs.modal', function(){
                  var ae = document.activeElement;
                  if (ae) ae.blur();
                });
                inst.show();
              }
            });
          </script>
          <?php } ?>
          <?php if (isset($error)) { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?>
          <?php
          $viewFile = __DIR__ . '/' . $template . '.php';
          if (file_exists($viewFile)) { require $viewFile; } else { echo 'View non trovata'; }
          ?>
        </div>
        <div class="py-3 px-3 text-center mt-auto">
          <div class="bg-white rounded p-3 shadow-sm">
            <p class="mb-0 fs-4">Design and Developed by <a href="https://techlabsomaschi.it/" target="_blank" class="pe-1 text-primary text-decoration-underline">TechLabSomaschi</a></p>
          </div>
        </div>
      </div>
    </div>
    <?php } ?>
  </div>
  <div class="dark-transparent sidebartoggler"></div>
  <script src="<?php echo Helpers::url('public/libs/bootstrap/dist/js/bootstrap.bundle.min.js'); ?>"></script>
  <script src="<?php echo Helpers::url('public/libs/simplebar/dist/simplebar.min.js'); ?>"></script>
  <script src="<?php echo Helpers::url('public/js/theme/app.init.js'); ?>"></script>
  <script src="<?php echo Helpers::url('public/js/theme/theme.js'); ?>"></script>
  <script src="<?php echo Helpers::url('public/js/theme/app.min.js'); ?>"></script>
  <script src="<?php echo Helpers::url('public/js/theme/sidebarmenu-default.js'); ?>"></script>
  <script src="<?php echo Helpers::url('public/libs/iconify/iconify-icon.min.js'); ?>"></script>
  <script>
    (function () {
      var u = '<?php echo Helpers::url('public/libs/iconify/solar.json'); ?>';
      fetch(u, { credentials: 'omit' }).then(function (r) { return r.json(); }).then(function (col) {
        var api = window.Iconify || window.IconifyIcon;
        if (api && api.addCollection) { api.addCollection(col); }
      });
    })();
  </script>
  <script src="<?php echo Helpers::url('public/js/highlights/highlight.min.js'); ?>"></script>
  <script src="<?php echo Helpers::url('public/libs/jquery/jquery-3.7.1.min.js'); ?>"></script>
  <?php
    $dtTemplates = [
      'students/index',
      'laptops/index',
      'customers/index',
      'work_groups/index',
      'payments/index',
      'logs/index',
      'software/index',
      'members/index',
      'memberships/index',
      'courses/index',
      'receipts/index',
    ];
    if (in_array(($template ?? ''), $dtTemplates, true)) {
  ?>
  <link rel="stylesheet" href="<?php echo Helpers::url('public/libs/datatables/jquery.dataTables.min.css'); ?>">
  <link rel="stylesheet" href="<?php echo Helpers::url('public/libs/datatables-buttons/buttons.dataTables.min.css'); ?>">
  <script src="<?php echo Helpers::url('public/libs/datatables/jquery.dataTables.min.js'); ?>"></script>
  <script src="<?php echo Helpers::url('public/libs/datatables-buttons/dataTables.buttons.min.js'); ?>"></script>
  <script src="<?php echo Helpers::url('public/libs/datatables-buttons/buttons.html5.min.js'); ?>"></script>
  <script src="<?php echo Helpers::url('public/libs/datatables-buttons/buttons.print.min.js'); ?>"></script>
  <script src="<?php echo Helpers::url('public/libs/datatables-buttons/buttons.colVis.min.js'); ?>"></script>
  <script src="<?php echo Helpers::url('public/libs/jszip/jszip.min.js'); ?>"></script>
  <script src="<?php echo Helpers::url('public/libs/pdfmake/pdfmake.min.js'); ?>"></script>
  <script src="<?php echo Helpers::url('public/libs/pdfmake/vfs_fonts.js'); ?>"></script>
  <?php } ?>
  <script>
  hljs.initHighlightingOnLoad();

  document.querySelectorAll("pre.code-view > code").forEach((codeBlock) => {
    codeBlock.textContent = codeBlock.innerHTML;
  });
  document.addEventListener('click', function(e){
    var t = e.target.closest('.export-csv');
    if (!t) return;
    var cnt = t.getAttribute('data-count') || '';
    var el = document.createElement('div');
    el.className = 'modal fade';
    el.innerHTML = '<div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Esportazione CSV</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div class="alert alert-info mb-0">Esportazione avviata'+(cnt?': '+cnt+' record':'')+'</div></div></div></div>';
    document.body.appendChild(el);
    var m = new bootstrap.Modal(el);
    m.show();
    setTimeout(function(){
      var ae = document.activeElement;
      if (ae) ae.blur();
      t.focus();
      el.addEventListener('hidden.bs.modal', function(){ el.remove(); }, { once: true });
      m.hide();
    }, 2000);
  });
</script>
</body>
</html>
