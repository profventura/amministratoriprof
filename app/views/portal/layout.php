<?php
use App\Core\Helpers;
$member = \App\Core\PublicAuth::user();
?>
<!DOCTYPE html>
<html lang="it" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" type="image/png" href="<?php echo Helpers::url('public/images/logos/favicon.png'); ?>" />
  <link rel="stylesheet" href="<?php echo Helpers::url('public/css/fonts-manrope.css'); ?>" />
  <link rel="stylesheet" href="<?php echo Helpers::url('public/css/styles.css'); ?>" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <title><?php echo htmlspecialchars($title ?? 'Area Soci'); ?></title>
  <style>
      .portal-header { background-color: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 15px 0; }
      .portal-nav .nav-link { color: #5a6a85; font-weight: 500; padding: 10px 15px; border-radius: 8px; transition: all 0.2s; }
      .portal-nav .nav-link:hover, .portal-nav .nav-link.active { background-color: #ecf2ff; color: #5d87ff; }
      .portal-content { padding: 30px 0; min-height: calc(100vh - 160px); }
      .portal-footer { background-color: #fff; padding: 20px 0; border-top: 1px solid #e1e6ef; }
  </style>
</head>
<body class="bg-light">
  
  <!-- Header -->
  <header class="portal-header sticky-top">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center">
        <a href="<?php echo Helpers::url('/portal/dashboard'); ?>" class="text-nowrap logo-img">
          <img src="<?php echo Helpers::url('public/images/logos/logo_v3_scuro.png'); ?>" width="150" alt="Logo" />
        </a>
        
        <div class="dropdown">
          <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
              <?php echo strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)); ?>
            </div>
            <span class="fw-semibold text-dark d-none d-md-block"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="dropdownUser">
            <li><a class="dropdown-item" href="<?php echo Helpers::url('/portal/profile'); ?>"><i class="ti ti-user me-2"></i> Profilo</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?php echo Helpers::url('/portal/logout'); ?>"><i class="ti ti-logout me-2"></i> Logout</a></li>
          </ul>
        </div>
      </div>
      
      <!-- Mobile Nav Toggle -->
      <button class="btn btn-link d-md-none w-100 mt-2 border-top pt-2 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav">
        Menu <i class="ti ti-chevron-down"></i>
      </button>
      
      <!-- Desktop Nav -->
      <nav class="portal-nav d-none d-md-flex mt-3 gap-2">
        <a href="<?php echo Helpers::url('/portal/dashboard'); ?>" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'active' : ''; ?>">
          <i class="ti ti-dashboard me-1"></i> Dashboard
        </a>
        <a href="<?php echo Helpers::url('/portal/profile'); ?>" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/profile') !== false ? 'active' : ''; ?>">
          <i class="ti ti-user-circle me-1"></i> Il Mio Profilo
        </a>
        <a href="<?php echo Helpers::url('/portal/payments'); ?>" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/payments') !== false ? 'active' : ''; ?>">
          <i class="ti ti-credit-card me-1"></i> Pagamenti
        </a>
      </nav>
      
      <!-- Mobile Nav Collapsible -->
      <div class="collapse d-md-none mt-2" id="mobileNav">
        <nav class="portal-nav d-flex flex-column gap-1">
            <a href="<?php echo Helpers::url('/portal/dashboard'); ?>" class="nav-link">Dashboard</a>
            <a href="<?php echo Helpers::url('/portal/profile'); ?>" class="nav-link">Profilo</a>
            <a href="<?php echo Helpers::url('/portal/payments'); ?>" class="nav-link">Pagamenti</a>
        </nav>
      </div>
    </div>
  </header>

  <!-- Content -->
  <div class="portal-content">
    <div class="container">
      
      <?php $flashes = Helpers::getFlashes(); ?>
      <?php if (!empty($flashes)) { ?>
      <div class="row">
        <div class="col-12">
          <?php foreach ($flashes as $f) { ?>
          <div class="alert alert-<?php echo $f['type']; ?> alert-dismissible fade show shadow-sm border-0" role="alert">
            <?php echo htmlspecialchars($f['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <?php } ?>
        </div>
      </div>
      <?php } ?>

      <?php
      // Include view content
      $viewFile = __DIR__ . '/' . str_replace('portal/', '', $template) . '.php';
      if (file_exists($viewFile)) { require $viewFile; } else { echo '<div class="alert alert-danger">Vista non trovata: ' . $template . '</div>'; }
      ?>
      
    </div>
  </div>

  <!-- Footer -->
  <footer class="portal-footer text-center text-muted">
    <div class="container">
      <p class="mb-0 fs-2">© <?php echo date('Y'); ?> Associazione Amministratori Professionisti. Tutti i diritti riservati.</p>
    </div>
  </footer>

  <script src="<?php echo Helpers::url('public/libs/bootstrap/dist/js/bootstrap.bundle.min.js'); ?>"></script>
</body>
</html>
