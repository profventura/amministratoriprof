<form method="post" action="<?php echo \App\Core\Helpers::url('/members'); ?>">
  <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
  <div class="row">
    <!-- Dati Personali -->
    <h5 class="mb-3">Dati Personali e Studio</h5>
    
    <div class="col-md-2 mb-3">
      <label class="form-label">N. Socio AP</label>
      <input type="text" name="member_number" class="form-control">
    </div>
    
    <div class="col-md-5 mb-3">
      <label class="form-label">Cognome *</label>
      <input type="text" name="last_name" class="form-control" required>
    </div>
    <div class="col-md-5 mb-3">
      <label class="form-label">Nome *</label>
      <input type="text" name="first_name" class="form-control" required>
    </div>
    
    <div class="col-md-6 mb-3">
      <label class="form-label">Nome Studio</label>
      <input type="text" name="studio_name" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Codice Fiscale</label>
      <input type="text" name="tax_code" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">CF / P.IVA Fatturazione</label>
      <input type="text" name="billing_cf_piva" class="form-control">
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">Revisore (54h)?</label>
      <select name="is_revisor" class="form-select">
        <option value="0">No</option>
        <option value="1">Sì</option>
      </select>
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">N. Revisione</label>
      <input type="text" name="revision_number" class="form-control">
    </div>

    <!-- Contatti -->
    <h5 class="mb-3 mt-4">Contatti e Indirizzo</h5>
    
    <div class="col-md-6 mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control">
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">Cellulare</label>
      <input type="text" name="mobile_phone" class="form-control">
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">Telefono Fisso</label>
      <input type="text" name="phone" class="form-control">
    </div>
    
    <div class="col-md-6 mb-3">
      <label class="form-label">Indirizzo</label>
      <input type="text" name="address" class="form-control">
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label">Città</label>
      <input type="text" name="city" class="form-control">
    </div>
    <div class="col-md-1 mb-3">
      <label class="form-label">Prov.</label>
      <input type="text" name="province" class="form-control" maxlength="2">
    </div>
    <div class="col-md-1 mb-3">
      <label class="form-label">CAP</label>
      <input type="text" name="zip_code" class="form-control" maxlength="10">
    </div>
    
    <!-- Altro -->
    <h5 class="mb-3 mt-4">Altre Info</h5>

    <div class="col-md-4 mb-3">
      <label class="form-label">Data di nascita</label>
      <input type="date" name="birth_date" class="form-control">
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label">Data Iscrizione</label>
      <input type="date" name="registration_date" class="form-control">
    </div>
    <div class="col-md-4 mb-3">
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

