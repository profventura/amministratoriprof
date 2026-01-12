<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Dettaglio Corso</h3>
  <div>
    <a href="<?php echo \App\Core\Helpers::url('/courses/'.$course['id'].'/edit'); ?>" class="btn btn-outline-primary">Modifica</a>
    <form method="post" action="<?php echo \App\Core\Helpers::url('/courses/'.$course['id'].'/delete'); ?>" class="d-inline">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      <button class="btn btn-outline-danger" onclick="return confirm('Eliminare il corso?')">Elimina</button>
    </form>
  </div>
</div>
<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <p><strong>Titolo:</strong> <?php echo htmlspecialchars($course['title']); ?></p>
        <p><strong>Descrizione:</strong> <?php echo htmlspecialchars($course['description']); ?></p>
      </div>
      <div class="col-md-6">
        <p><strong>Data:</strong> <?php echo htmlspecialchars($course['course_date']); ?></p>
        <p><strong>Orario:</strong> <?php echo htmlspecialchars(($course['start_time'] ?? '').' - '.($course['end_time'] ?? '')); ?></p>
        <p><strong>Anno:</strong> <?php echo (int)$course['year']; ?></p>
      </div>
    </div>
  </div>
</div>
<div class="card mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">Partecipanti</h5>
      <form method="post" action="<?php echo \App\Core\Helpers::url('/courses/'.$course['id'].'/participants/add'); ?>" class="d-flex">
        <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
        <select name="member_id" class="form-select me-2" required>
          <option value="">Seleziona socio</option>
          <?php foreach ($members as $m) { ?>
          <option value="<?php echo (int)$m['id']; ?>"><?php echo htmlspecialchars($m['last_name'].' '.$m['first_name']); ?></option>
          <?php } ?>
        </select>
        <button class="btn btn-secondary">Aggiungi</button>
      </form>
    </div>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Cognome</th><th>Nome</th><th>Email</th><th>Attestato</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($participants as $p) { ?>
          <tr>
            <td><?php echo htmlspecialchars($p['last_name']); ?></td>
            <td><?php echo htmlspecialchars($p['first_name']); ?></td>
            <td><?php echo htmlspecialchars($p['email']); ?></td>
            <td>
              <?php if (!empty($p['certificate_document_id'])) { ?>
                <a class="btn btn-sm btn-outline-primary" href="<?php echo \App\Core\Helpers::url('/documents/'.$p['certificate_document_id'].'/download'); ?>">Scarica</a>
              <?php } else { ?>
                <form method="post" action="<?php echo \App\Core\Helpers::url('/documents/dm-certificate/'.$course['id'].'/generate'); ?>" class="d-inline">
                  <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
                  <input type="hidden" name="member_id" value="<?php echo (int)$p['member_id']; ?>">
                  <button class="btn btn-sm btn-outline-secondary">Genera</button>
                </form>
              <?php } ?>
            </td>
            <td class="text-end">
              <form method="post" action="<?php echo \App\Core\Helpers::url('/courses/'.$course['id'].'/participants/remove'); ?>" class="d-inline">
                <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
                <input type="hidden" name="member_id" value="<?php echo (int)$p['member_id']; ?>">
                <button class="btn btn-sm btn-outline-danger">Rimuovi</button>
              </form>
            </td>
          </tr>
          <?php } ?>
          <?php if (empty($participants)) { ?>
          <tr><td colspan="5" class="text-center text-muted py-4">Nessun partecipante</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <form method="post" action="<?php echo \App\Core\Helpers::url('/documents/dm-certificate/'.$course['id'].'/generate-mass'); ?>" class="mt-3">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      <button class="btn btn-primary">Genera attestati per tutti</button>
    </form>
  </div>
</div>
