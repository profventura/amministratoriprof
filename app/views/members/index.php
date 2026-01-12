<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Soci</h3>
  <a href="<?php echo \App\Core\Helpers::url('/members/create'); ?>" class="btn btn-primary">Nuovo Socio</a>
</div>
<form class="row g-2 mb-3" method="get" action="<?php echo \App\Core\Helpers::url('/members'); ?>">
  <div class="col-sm-4">
    <input type="text" name="q" value="<?php echo htmlspecialchars($filters['q'] ?? ''); ?>" class="form-control" placeholder="Cerca">
  </div>
  <div class="col-sm-3">
    <select name="status" class="form-select">
      <option value="">Tutti gli stati</option>
      <option value="active" <?php echo (($filters['status'] ?? '')==='active')?'selected':''; ?>>Attivo</option>
      <option value="inactive" <?php echo (($filters['status'] ?? '')==='inactive')?'selected':''; ?>>Inattivo</option>
    </select>
  </div>
  <div class="col-sm-2">
    <button class="btn btn-secondary w-100">Filtra</button>
  </div>
</form>
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>Cognome</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Telefono</th>
            <th>Città</th>
            <th>Stato</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r) { ?>
          <tr>
            <td><?php echo htmlspecialchars($r['last_name']); ?></td>
            <td><?php echo htmlspecialchars($r['first_name']); ?></td>
            <td><?php echo htmlspecialchars($r['email']); ?></td>
            <td><?php echo htmlspecialchars($r['phone']); ?></td>
            <td><?php echo htmlspecialchars($r['city']); ?></td>
            <td><?php echo htmlspecialchars($r['status']); ?></td>
            <td class="text-end">
              <a href="<?php echo \App\Core\Helpers::url('/members/'.$r['id']); ?>" class="btn btn-sm btn-outline-primary">Apri</a>
            </td>
          </tr>
          <?php } ?>
          <?php if (empty($rows)) { ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Nessun socio trovato</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

