<form method="post" action="<?php echo \App\Core\Helpers::url('/ap/payments'); ?>">
  <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">Socio</label>
      <select name="member_id" class="form-select" required>
        <option value="">Seleziona</option>
        <?php foreach ($members as $m) { ?>
          <option value="<?php echo (int)$m['id']; ?>"><?php echo htmlspecialchars($m['last_name'].' '.$m['first_name']); ?></option>
        <?php } ?>
      </select>
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">Anno</label>
      <input type="number" name="year" class="form-control" value="<?php echo (int)$year; ?>" required>
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">Data pagamento</label>
      <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">Importo</label>
      <input type="number" step="0.01" min="0" name="amount" class="form-control" required>
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label">Metodo</label>
      <select name="method" class="form-select">
        <option value="bank">Bonifico</option>
        <option value="cash">Contanti</option>
        <option value="card">Carta</option>
      </select>
    </div>
    <div class="col-md-12 mb-3">
      <label class="form-label">Note</label>
      <input type="text" name="notes" class="form-control">
    </div>
  </div>
  <button class="btn btn-primary">Registra pagamento</button>
  <a href="<?php echo \App\Core\Helpers::url('/memberships?year='.(int)$year); ?>" class="btn btn-secondary">Annulla</a>
</form>

