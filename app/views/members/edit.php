<form method="post" action="<?php echo \App\Core\Helpers::url('/members/'.$row['id'].'/update'); ?>">
  <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">Nome</label>
      <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($row['first_name']); ?>" required>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Cognome</label>
      <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($row['last_name']); ?>" required>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Telefono</label>
      <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($row['phone']); ?>">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Indirizzo</label>
      <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($row['address']); ?>">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Città</label>
      <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($row['city']); ?>">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Data di nascita</label>
      <input type="date" name="birth_date" class="form-control" value="<?php echo htmlspecialchars($row['birth_date']); ?>">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Codice Fiscale</label>
      <input type="text" name="tax_code" class="form-control" value="<?php echo htmlspecialchars($row['tax_code']); ?>">
    </div>
    <div class="col-md-6 mb-3">
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

