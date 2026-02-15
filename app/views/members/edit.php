<form method="post" action="<?php echo \App\Core\Helpers::url('/members/'.$row['id'].'/update'); ?>">
  <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
  <div class="row">
    <!-- Dati Personali -->
    <h5 class="mb-3">Dati Personali e Studio</h5>
    
    <div class="col-md-2 mb-3">
      <label class="form-label">N. Socio AP</label>
      <input type="text" name="member_number" class="form-control" value="<?php echo htmlspecialchars($row['member_number'] ?? ''); ?>">
    </div>
    
    <div class="col-md-5 mb-3">
      <label class="form-label">Cognome *</label>
      <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($row['last_name']); ?>" required>
    </div>
    <div class="col-md-5 mb-3">
      <label class="form-label">Nome *</label>
      <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($row['first_name']); ?>" required>
    </div>
    
    <div class="col-md-6 mb-3">
      <label class="form-label">Nome Studio</label>
      <input type="text" name="studio_name" class="form-control" value="<?php echo htmlspecialchars($row['studio_name'] ?? ''); ?>">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Codice Fiscale</label>
      <input type="text" name="tax_code" class="form-control" value="<?php echo htmlspecialchars($row['tax_code']); ?>">
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">CF Fatturazione</label>
      <input type="text" name="billing_cf" class="form-control" value="<?php echo htmlspecialchars($row['billing_cf'] ?? ''); ?>">
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">P.IVA Fatturazione</label>
      <input type="text" name="billing_piva" class="form-control" value="<?php echo htmlspecialchars($row['billing_piva'] ?? ''); ?>">
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">Revisore (54h)?</label>
      <select name="is_revisor" class="form-select">
        <option value="0" <?php echo ($row['is_revisor'] ?? 0) == 0 ? 'selected' : ''; ?>>No</option>
        <option value="1" <?php echo ($row['is_revisor'] ?? 0) == 1 ? 'selected' : ''; ?>>Sì</option>
      </select>
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">N. Revisione</label>
      <input type="text" name="revision_number" class="form-control" value="<?php echo htmlspecialchars($row['revision_number'] ?? ''); ?>">
    </div>

    <!-- Accesso Area Riservata -->
    <h5 class="mb-3 mt-4">Accesso Area Riservata</h5>
    <div class="col-md-6 mb-3">
      <label class="form-label">Username</label>
      <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['username'] ?? ''); ?>" disabled readonly>
      <div class="form-text">L'username non è modificabile.</div>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" placeholder="Lasciare vuoto per non modificare">
    </div>

    <!-- Contatti -->
    <h5 class="mb-3 mt-4">Contatti e Indirizzo</h5>
    
    <div class="col-md-6 mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>">
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">Cellulare</label>
      <input type="text" name="mobile_phone" class="form-control" value="<?php echo htmlspecialchars($row['mobile_phone'] ?? ''); ?>">
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">Telefono Fisso</label>
      <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($row['phone']); ?>">
    </div>
    
    <div class="col-md-6 mb-3">
      <label class="form-label">Indirizzo</label>
      <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($row['address']); ?>">
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label">Città</label>
      <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($row['city']); ?>">
    </div>
    <div class="col-md-1 mb-3">
      <label class="form-label">Prov.</label>
      <input type="text" name="province" class="form-control" value="<?php echo htmlspecialchars($row['province'] ?? ''); ?>" maxlength="2">
    </div>
    <div class="col-md-1 mb-3">
      <label class="form-label">CAP</label>
      <input type="text" name="zip_code" class="form-control" value="<?php echo htmlspecialchars($row['zip_code'] ?? ''); ?>" maxlength="10">
    </div>
    
    <!-- Altro -->
    <h5 class="mb-3 mt-4">Altre Info</h5>

    <div class="col-md-4 mb-3">
      <label class="form-label">Data di nascita</label>
      <input type="date" name="birth_date" class="form-control" value="<?php echo htmlspecialchars($row['birth_date']); ?>">
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label">Data Iscrizione</label>
      <input type="date" name="registration_date" class="form-control" value="<?php echo htmlspecialchars($row['registration_date'] ?? ''); ?>">
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label">Stato</label>
      <select name="status" class="form-select">
        <option value="active" <?php echo $row['status']==='active'?'selected':''; ?>>Attivo</option>
        <option value="inactive" <?php echo $row['status']==='inactive'?'selected':''; ?>>Inattivo</option>
      </select>
    </div>
  </div>
  <button class="btn btn-primary">Aggiorna</button>
  <a href="<?php echo \App\Core\Helpers::url('/members/'.$row['id']); ?>" class="btn btn-secondary">Annulla</a>
</form>

