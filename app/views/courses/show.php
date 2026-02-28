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
      <div class="d-flex">
          <button type="button" class="btn btn-outline-success me-2" data-bs-toggle="modal" data-bs-target="#addMassiveModal">
              <i class="ti ti-users-plus"></i> Aggiungi Massivo
          </button>
          
          <form method="post" action="<?php echo \App\Core\Helpers::url('/courses/'.$course['id'].'/participants/add'); ?>" class="d-flex">
            <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
            <select name="member_id" class="form-select me-2" required style="width: 200px;">
              <option value="">Seleziona socio</option>
              <?php foreach ($members as $m) { 
                  // Filtra soci già presenti (opzionale, ma utile per UX)
                  $alreadyIn = false;
                  foreach($participants as $p) { if($p['member_id'] == $m['id']) { $alreadyIn=true; break; } }
                  if(!$alreadyIn) {
              ?>
              <option value="<?php echo (int)$m['id']; ?>"><?php echo htmlspecialchars($m['last_name'].' '.$m['first_name']); ?></option>
              <?php } } ?>
            </select>
            <button class="btn btn-secondary">Aggiungi</button>
          </form>
      </div>
    </div>
    
    <!-- Modal Aggiunta Massiva -->
    <div class="modal fade" id="addMassiveModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Aggiungi Partecipanti Massivamente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="post" action="<?php echo \App\Core\Helpers::url('/courses/'.$course['id'].'/participants/add'); ?>">
              <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
              <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="searchMemberInput" class="form-control" placeholder="Cerca socio per nome o cognome...">
                </div>
                <div class="list-group" id="membersList" style="max-height: 400px; overflow-y: auto;">
                    <?php 
                    $countAvailable = 0;
                    foreach ($members as $m) { 
                        $alreadyIn = false;
                        foreach($participants as $p) { if($p['member_id'] == $m['id']) { $alreadyIn=true; break; } }
                        if(!$alreadyIn) {
                            $countAvailable++;
                    ?>
                    <label class="list-group-item">
                        <input class="form-check-input me-1" type="checkbox" name="member_ids[]" value="<?php echo $m['id']; ?>" data-name="<?php echo strtolower($m['last_name'].' '.$m['first_name']); ?>">
                        <?php echo htmlspecialchars($m['last_name'].' '.$m['first_name']); ?>
                        <small class="text-muted ms-2">(<?php echo htmlspecialchars($m['email']); ?>)</small>
                    </label>
                    <?php } } ?>
                    <?php if ($countAvailable === 0) { ?>
                        <div class="alert alert-info">Tutti i soci sono già iscritti a questo corso.</div>
                    <?php } ?>
                </div>
                <div class="mt-2 text-end">
                    <small id="selectedCount" class="text-muted">0 selezionati</small>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                <button type="submit" class="btn btn-primary">Aggiungi Selezionati</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchMemberInput');
        const membersList = document.getElementById('membersList');
        const labels = membersList.querySelectorAll('label.list-group-item');
        const checkboxes = membersList.querySelectorAll('input[type="checkbox"]');
        const selectedCountSpan = document.getElementById('selectedCount');

        // Filtro ricerca
        searchInput.addEventListener('keyup', function() {
            const term = this.value.toLowerCase();
            labels.forEach(label => {
                const input = label.querySelector('input');
                const name = input.getAttribute('data-name');
                if (name.includes(term)) {
                    label.style.display = '';
                } else {
                    label.style.display = 'none';
                }
            });
        });

        // Contatore selezionati
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateCount);
        });

        function updateCount() {
            const count = membersList.querySelectorAll('input[type="checkbox"]:checked').length;
            selectedCountSpan.textContent = count + ' selezionati';
        }
    });
    </script>

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
                <div class="btn-group btn-group-sm">
                    <a class="btn btn-outline-primary" href="<?php echo \App\Core\Helpers::url('/documents/'.$p['certificate_document_id'].'/download'); ?>" title="Scarica">
                        <i class="ti ti-download"></i>
                    </a>
                    <form method="post" action="<?php echo \App\Core\Helpers::url('/documents/'.$p['certificate_document_id'].'/email'); ?>" class="d-inline" onsubmit="return confirm('Inviare l\'attestato via email a <?php echo htmlspecialchars($p['email']); ?>?');">
                        <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
                        <button class="btn btn-outline-info" title="Invia Email"><i class="ti ti-mail"></i></button>
                    </form>
                </div>
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
