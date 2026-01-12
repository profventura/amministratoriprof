<form method="post" action="<?php echo \App\Core\Helpers::url('/members'); ?>">
  <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">Nome</label>
      <input type="text" name="first_name" class="form-control" required>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Cognome</label>
      <input type="text" name="last_name" class="form-control" required>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Telefono</label>
      <input type="text" name="phone" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Indirizzo</label>
      <input type="text" name="address" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Città</label>
      <input type="text" name="city" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Data di nascita</label>
      <input type="date" name="birth_date" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Codice Fiscale</label>
      <input type="text" name="tax_code" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Stato</label>
      <select name="status" class="form-select">
        <option value="active">Attivo</option>
        <option value="inactive">Inattivo</option>
      </select>
    </div>
  </div>
  <button class="btn btn-primary">Salva</button>
  <a href="<?php echo \App\Core\Helpers::url('/members'); ?>" class="btn btn-secondary">Annulla</a>
</form>

