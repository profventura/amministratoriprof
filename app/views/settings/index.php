<?php
use App\Core\Helpers;
$row = $data['row'] ?? [];
?>

<div class="card">
  <div class="card-body">
    <h4 class="mb-3">Indirizzo Pubblico Area Soci</h4>
    <p class="card-text">Inserisci l'URL completo (es. <code>https://www.miosito.it/portal</code>) dove sarà raggiungibile l'area soci pubblica. Questo link verrà usato nelle email e nei documenti.</p>
    <form method="post" action="<?php echo \App\Core\Helpers::url('/settings/update-public-url'); ?>">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      <div class="input-group mb-3">
        <span class="input-group-text">URL Pubblico</span>
        <input type="url" name="public_url" class="form-control" placeholder="https://..." value="<?php echo htmlspecialchars($row['public_url'] ?? ''); ?>">
        <button class="btn btn-primary" type="submit">Salva URL</button>
      </div>
    </form>
    
    <div class="mt-2">
        <label class="form-label">Anteprima Accesso Area Soci</label>
        <div class="input-group">
            <input type="text" class="form-control" value="<?php echo \App\Core\Helpers::url('/portal/login'); ?>" readonly>
            <a href="<?php echo \App\Core\Helpers::url('/portal/login'); ?>" target="_blank" class="btn btn-outline-secondary">
                <i class="ti ti-external-link"></i> Apri in nuova scheda
            </a>
        </div>
        <div class="form-text">Puoi usare questo link per testare l'accesso o condividerlo con i soci.</div>
    </div>
    
    <hr class="my-4">
    <h4 class="mb-3">Credenziali Amministratore</h4>
    <p class="text-muted small">Modifica qui l'username e la password per l'accesso al pannello di amministrazione.</p>
    
    <form method="post" action="<?php echo \App\Core\Helpers::url('/settings/update-admin-credentials'); ?>">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars(\App\Core\Auth::user()['username']); ?>" required>
      </div>
      
      <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Nuova Password</label>
            <input type="password" name="password" class="form-control" placeholder="Lasciare vuoto per mantenere attuale">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Conferma Password</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Ripeti nuova password">
          </div>
      </div>
      
      <button class="btn btn-primary" type="submit">Aggiorna Credenziali</button>
    </form>
  </div>
</div>
