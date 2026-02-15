<?php
use App\Core\Helpers;
$config = require __DIR__ . '/../../config.php';
?>
<!DOCTYPE html>
<html lang="it" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" type="image/png" href="<?php echo Helpers::url('public/images/logos/favicon.png'); ?>" />
  <link rel="stylesheet" href="<?php echo Helpers::url('public/css/fonts-manrope.css'); ?>" />
  <link rel="stylesheet" href="<?php echo Helpers::url('public/css/styles.css'); ?>" />
  <title><?php echo htmlspecialchars($title ?? 'Area Soci'); ?></title>
</head>
<body class="bg-primary-subtle">
  <div class="position-relative overflow-hidden radial-gradient min-vh-100 w-100">
    <div class="position-relative z-index-5">
      <div class="row gx-0">
        <div class="col-lg-6 col-xl-5 col-xxl-4 mx-auto">
          <div class="min-vh-100 row justify-content-center align-items-center p-5">
            <div class="col-12 auth-card bg-white rounded-4 p-4 shadow-lg">
              <div class="text-center mb-4">
                <a href="<?php echo Helpers::url('/'); ?>" class="text-nowrap logo-img d-block w-100">
                  <img src="<?php echo Helpers::url('public/images/logos/logo_v3_scuro.png'); ?>" width="200" alt="Logo" />
                </a>
                <h4 class="mt-3">Area Riservata Soci</h4>
              </div>
              
              <?php $flashes = Helpers::getFlashes(); ?>
              <?php if (!empty($flashes)) { ?>
                  <?php foreach ($flashes as $f) { ?>
                  <div class="alert alert-<?php echo $f['type']; ?> mb-3">
                    <?php echo htmlspecialchars($f['message']); ?>
                  </div>
                  <?php } ?>
              <?php } ?>

              <form action="<?php echo Helpers::url('/portal/login'); ?>" method="post">
                <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
                <div class="mb-3">
                  <label for="username" class="form-label">Username</label>
                  <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>
                <div class="mb-4">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">Accedi</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="<?php echo Helpers::url('public/libs/bootstrap/dist/js/bootstrap.bundle.min.js'); ?>"></script>
</body>
</html>
