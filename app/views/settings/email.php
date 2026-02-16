<?php
use App\Core\Helpers;
$row = $data['row'] ?? [];
?>

<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="card-title mb-0">Impostazioni Email</h4>
        <a href="<?php echo Helpers::url('/settings'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-arrow-left"></i> Torna a Settings
        </a>
    </div>

    <div class="alert alert-info">
        Configura i parametri SMTP per l'invio delle email dal sistema.
    </div>

    <hr class="my-4">

    <form method="post" action="<?php echo Helpers::url('/settings/email/update'); ?>">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      
      <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Host SMTP</label>
            <input type="text" name="smtp_host" class="form-control" placeholder="es. smtp.gmail.com" value="<?php echo htmlspecialchars($row['smtp_host'] ?? ''); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Porta SMTP</label>
            <input type="number" name="smtp_port" class="form-control" placeholder="es. 587" value="<?php echo htmlspecialchars($row['smtp_port'] ?? ''); ?>">
          </div>
          
          <div class="col-md-6">
            <label class="form-label">Username SMTP</label>
            <input type="text" name="smtp_user" class="form-control" placeholder="es. tuaemail@gmail.com" value="<?php echo htmlspecialchars($row['smtp_user'] ?? ''); ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Password SMTP</label>
            <input type="password" name="smtp_pass" class="form-control" placeholder="Lasciare vuoto per non modificare">
          </div>
          
          <div class="col-md-6">
            <label class="form-label">Email Mittente (From)</label>
            <input type="email" name="smtp_from" class="form-control" placeholder="es. no-reply@tuosito.it" value="<?php echo htmlspecialchars($row['smtp_from'] ?? ''); ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Nome Mittente</label>
            <input type="text" name="smtp_from_name" class="form-control" placeholder="es. Associazione AP" value="<?php echo htmlspecialchars($row['smtp_from_name'] ?? ''); ?>">
          </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">Salva Configurazione</button>
        <button type="button" class="btn btn-outline-warning" onclick="alert('Funzionalità di test email non ancora implementata.')">Test Invio Email</button>
      </div>
    </form>
  </div>
</div>
