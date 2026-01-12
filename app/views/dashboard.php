<h2 class="mb-4">Dashboard</h2>
<div class="row">
  <div class="col-lg-3">
    <div class="card bg-primary-subtle">
      <div class="card-body">
        <h6 class="card-title mb-1 text-center">Soci totali</h6>
        <h2 class="text-primary text-center"><?php echo (int)($counts['members_total'] ?? 0); ?></h2>
      </div>
    </div>
  </div>
  <div class="col-lg-3">
    <div class="card bg-success-subtle">
      <div class="card-body">
        <h6 class="card-title mb-1 text-center">In regola (anno)</h6>
        <h2 class="text-success text-center"><?php echo (int)($counts['regular_current_year'] ?? 0); ?></h2>
      </div>
    </div>
  </div>
  <div class="col-lg-3">
    <div class="card bg-warning-subtle">
      <div class="card-body">
        <h6 class="card-title mb-1 text-center">Non in regola (anno)</h6>
        <h2 class="text-warning text-center"><?php echo (int)($counts['not_regular_current_year'] ?? 0); ?></h2>
      </div>
    </div>
  </div>
  <div class="col-lg-3">
    <div class="card bg-info-subtle">
      <div class="card-body">
        <h6 class="card-title mb-1 text-center">Pagamenti (anno)</h6>
        <h2 class="text-info text-center"><?php echo (int)($counts['payments_current_year'] ?? 0); ?></h2>
      </div>
    </div>
  </div>
</div>
<div class="row mt-4">
  <div class="col-lg-3">
    <div class="card bg-secondary-subtle">
      <div class="card-body">
        <h6 class="card-title mb-1 text-center">Documenti (anno)</h6>
        <h2 class="text-secondary text-center"><?php echo (int)($counts['documents_current_year'] ?? 0); ?></h2>
      </div>
    </div>
  </div>
  <div class="col-lg-3">
    <div class="card bg-success-subtle">
      <div class="card-body">
        <h6 class="card-title mb-1 text-center">Entrate</h6>
        <h2 class="text-success text-center">€ <?php echo number_format((float)($counts['cash_income'] ?? 0), 2, ',', '.'); ?></h2>
      </div>
    </div>
  </div>
  <div class="col-lg-3">
    <div class="card bg-danger-subtle">
      <div class="card-body">
        <h6 class="card-title mb-1 text-center">Uscite</h6>
        <h2 class="text-danger text-center">€ <?php echo number_format((float)($counts['cash_expense'] ?? 0), 2, ',', '.'); ?></h2>
      </div>
    </div>
  </div>
  <div class="col-lg-3">
    <div class="card bg-primary-subtle">
      <div class="card-body">
        <h6 class="card-title mb-1 text-center">Saldo</h6>
        <h2 class="text-primary text-center">€ <?php echo number_format((float)($counts['cash_balance'] ?? 0), 2, ',', '.'); ?></h2>
      </div>
    </div>
  </div>
</div>
