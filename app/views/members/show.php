<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Scheda Socio</h3>
  <div>
    <a href="<?php echo \App\Core\Helpers::url('/members/'.$row['id'].'/edit'); ?>" class="btn btn-outline-primary">Modifica</a>
    <form method="post" action="<?php echo \App\Core\Helpers::url('/members/'.$row['id'].'/delete'); ?>" class="d-inline">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      <button class="btn btn-outline-danger" onclick="return confirm('Disattivare il socio?')">Disattiva</button>
    </form>
  </div>
</div>
<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <p><strong>Nome:</strong> <?php echo htmlspecialchars($row['first_name']); ?></p>
        <p><strong>Cognome:</strong> <?php echo htmlspecialchars($row['last_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
        <p><strong>Telefono:</strong> <?php echo htmlspecialchars($row['phone']); ?></p>
        <p><strong>Indirizzo:</strong> <?php echo htmlspecialchars($row['address']); ?></p>
      </div>
      <div class="col-md-6">
        <p><strong>Città:</strong> <?php echo htmlspecialchars($row['city']); ?></p>
        <p><strong>Data di nascita:</strong> <?php echo htmlspecialchars($row['birth_date']); ?></p>
        <p><strong>Codice Fiscale:</strong> <?php echo htmlspecialchars($row['tax_code']); ?></p>
        <p><strong>Stato:</strong> <?php echo htmlspecialchars($row['status']); ?></p>
      </div>
    </div>
  </div>
</div>

<?php
  $docsModel = new \App\Models\Document();
  $docs = $docsModel->byMember((int)$row['id']);
?>
<div class="card mt-3">
  <div class="card-body">
    <h5 class="mb-3">Documenti</h5>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Tipo</th><th>Anno</th><th>File</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($docs as $d) { ?>
          <tr>
            <td><?php echo htmlspecialchars($d['type']); ?></td>
            <td><?php echo (int)$d['year']; ?></td>
            <td><?php echo htmlspecialchars(basename($d['file_path'])); ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="<?php echo \App\Core\Helpers::url('/documents/'.$d['id'].'/download'); ?>">Scarica</a>
            </td>
          </tr>
          <?php } ?>
          <?php if (empty($docs)) { ?>
          <tr><td colspan="4" class="text-center text-muted">Nessun documento</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer">
    <a href="<?php echo \App\Core\Helpers::url('/ap/payments/create'); ?>" class="btn btn-primary">Genera ricevuta (nuovo pagamento)</a>
  </div>
</div>

<div class="card mt-3">
  <div class="card-body">
    <h5 class="mb-3">Certificato di appartenenza</h5>
    <form method="post" action="<?php echo \App\Core\Helpers::url('/documents/membership-certificate/generate'); ?>" class="row g-2">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      <input type="hidden" name="member_id" value="<?php echo (int)$row['id']; ?>">
      <div class="col-sm-3">
        <input type="number" name="year" class="form-control" value="<?php echo (int)date('Y'); ?>">
      </div>
      <div class="col-sm-3">
        <button class="btn btn-secondary">Genera certificato</button>
      </div>
    </form>
  </div>
 </div>
