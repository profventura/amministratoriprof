<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Iscrizioni <?php echo (int)$year; ?></h3>
  <div class="d-flex">
    <form class="d-flex me-2" method="get" action="<?php echo \App\Core\Helpers::url('/memberships'); ?>">
      <input type="number" class="form-control me-2" name="year" value="<?php echo (int)$year; ?>">
      <button class="btn btn-secondary">Vai</button>
    </form>
    <form method="post" action="<?php echo \App\Core\Helpers::url('/documents/membership-certificate/generate-mass'); ?>">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      <input type="hidden" name="year" value="<?php echo (int)$year; ?>">
      <button class="btn btn-primary">Genera certificati (in regola)</button>
    </form>
  </div>
</div>
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>Cognome</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Stato</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r) { ?>
          <tr>
            <td><?php echo htmlspecialchars($r['last_name']); ?></td>
            <td><?php echo htmlspecialchars($r['first_name']); ?></td>
            <td><?php echo htmlspecialchars($r['email']); ?></td>
            <td><?php echo htmlspecialchars($r['status']); ?></td>
          </tr>
          <?php } ?>
          <?php if (empty($rows)) { ?>
          <tr><td colspan="4" class="text-center text-muted py-4">Nessuna iscrizione per l'anno selezionato</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

