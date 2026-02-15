<div class="row mb-3">
  <div class="col-md-4">
    <div class="card bg-primary-subtle">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-center round-48 rounded text-bg-primary flex-shrink-0 mb-3 mx-auto">
          <iconify-icon icon="solar:diploma-verified-outline" class="icon-24 text-white"></iconify-icon>
        </div>
        <h5 class="card-title fw-semibold text-center mb-1">Totale Iscrizioni <?php echo $year; ?></h5>
        <h2 class="card-text text-primary text-center"><?php echo count($rows); ?></h2>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card bg-success-subtle">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-center round-48 rounded text-bg-success flex-shrink-0 mb-3 mx-auto">
          <iconify-icon icon="solar:check-circle-line-duotone" class="icon-24 text-white"></iconify-icon>
        </div>
        <h5 class="card-title fw-semibold text-center mb-1">In Regola</h5>
        <?php $regular = array_filter($rows, fn($r) => $r['status'] === 'regular'); ?>
        <h2 class="card-text text-success text-center"><?php echo count($regular); ?></h2>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card bg-warning-subtle">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-center round-48 rounded text-bg-warning flex-shrink-0 mb-3 mx-auto">
          <iconify-icon icon="solar:clock-circle-line-duotone" class="icon-24 text-white"></iconify-icon>
        </div>
        <h5 class="card-title fw-semibold text-center mb-1">In Attesa</h5>
        <?php $pending = array_filter($rows, fn($r) => $r['status'] === 'pending'); ?>
        <h2 class="card-text text-warning text-center"><?php echo count($pending); ?></h2>
      </div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Elenco Iscrizioni</h3>
  <div class="d-flex gap-2">
    <form id="bulkForm" action="<?php echo \App\Core\Helpers::url('/memberships/bulk-action'); ?>" method="post" class="d-flex gap-2">
        <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
        <input type="hidden" name="year" value="<?php echo (int)$year; ?>">
        <select name="action" class="form-select" style="width: 200px;" required>
            <option value="">Azioni di gruppo...</option>
            <option value="generate_certificate">Genera Certificati</option>
            <option value="delete">Elimina</option>
        </select>
        <button type="submit" class="btn btn-outline-secondary">Applica</button>
    </form>
    
    <form class="d-flex ms-3" method="get" action="<?php echo \App\Core\Helpers::url('/memberships'); ?>">
      <input type="number" class="form-control me-2" name="year" value="<?php echo (int)$year; ?>" style="width: 100px;">
      <button class="btn btn-outline-primary">Cambia Anno</button>
    </form>
  </div>
</div>

<div class="table-responsive">
  <table id="datatable" class="table table-striped table-bordered text-nowrap">
    <thead class="table-light">
      <tr>
        <th width="40"><input type="checkbox" id="selectAll" class="form-check-input"></th>
        <th>Cognome</th>
        <th>Nome</th>
        <th>Email</th>
        <th>Stato</th>
        <th>Data Rinnovo</th>
        <th>Azioni</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r) { ?>
      <tr>
        <td><input type="checkbox" name="selected_ids[]" value="<?php echo $r['id']; ?>" class="form-check-input" form="bulkForm"></td>
        <td><?php echo htmlspecialchars($r['last_name']); ?></td>
        <td><?php echo htmlspecialchars($r['first_name']); ?></td>
        <td><?php echo htmlspecialchars($r['email']); ?></td>
        <td>
            <?php if ($r['status'] === 'regular') { ?>
                <span class="badge bg-success">In Regola</span>
            <?php } elseif ($r['status'] === 'pending') { ?>
                <span class="badge bg-warning">In Attesa</span>
            <?php } else { ?>
                <span class="badge bg-danger">Scaduto</span>
            <?php } ?>
        </td>
        <td><?php echo htmlspecialchars($r['renewal_date'] ?? '-'); ?></td>
        <td>
          <a href="<?php echo \App\Core\Helpers::url('/memberships/'.$r['id'].'/edit'); ?>" class="btn btn-sm btn-outline-warning">Modifica</a>
          <form action="<?php echo \App\Core\Helpers::url('/memberships/' . $r['id'] . '/delete'); ?>" method="post" class="d-inline delete-form">
            <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger">Elimina</button>
          </form>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<!-- Modale di Conferma Eliminazione -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Conferma Eliminazione</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="deleteConfirmMessage">Sei sicuro di voler procedere?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Elimina</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    if (!window.jQuery) return;
    var $ = window.jQuery;
    
    // Variabile per memorizzare il form da inviare
    var formToSubmit = null;
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    
    // Gestione Select All
    $('#selectAll').on('click', function() {
        var rows = $('#datatable').DataTable().rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });

    // Gestione eliminazione singola
    $(document).on('submit', 'form.delete-form', function(e){
        e.preventDefault();
        formToSubmit = this;
        $('#deleteConfirmMessage').text('Sei sicuro di voler eliminare questa iscrizione?');
        deleteModal.show();
    });

    // Gestione azione massiva
    $('#bulkForm').on('submit', function(e){
        var action = $(this).find('select[name="action"]').val();
        
        // Conta i selezionati
        var count = $('input[name="selected_ids[]"]:checked').length;
        if (count === 0) {
            alert('Seleziona almeno una iscrizione.');
            e.preventDefault();
            return;
        }

        if (action === 'delete') {
            e.preventDefault();
            formToSubmit = this;
            $('#deleteConfirmMessage').text('Sei sicuro di voler eliminare ' + count + ' iscrizioni selezionate?');
            deleteModal.show();
        } 
        // Per 'generate_certificate' o altre azioni non distruttive, procedi direttamente
    });

    // Click su conferma nel modale
    $('#confirmDeleteBtn').on('click', function(){
        if (formToSubmit) {
            formToSubmit.submit();
        }
        deleteModal.hide();
    });

    $('#datatable').DataTable({
        responsive: true,
        deferRender: true,
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, "All"]],
        order: [[0, 'asc']], // Ordina per Cognome di default
        columnDefs: [
            { targets: -1, orderable: false, searchable: false }
        ],
        dom: 'B<"d-flex justify-content-end align-items-center"f>rt<"d-flex justify-content-between align-items-center mt-2"l i p>',
        buttons: [
            { extend: 'copy', text: 'Copia', className: 'btn btn-outline-primary' },
            { extend: 'csv', text: 'CSV', className: 'btn btn-outline-primary' },
            { extend: 'excel', text: 'Excel', className: 'btn btn-outline-primary' },
            { extend: 'pdf', text: 'PDF', className: 'btn btn-outline-primary' },
            { extend: 'print', text: 'Stampa', className: 'btn btn-outline-primary' },
            { extend: 'colvis', text: 'Colonne', className: 'btn btn-outline-primary' }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/it-IT.json',
            search: 'Cerca:',
            lengthMenu: 'Mostra _MENU_ righe',
            info: 'Mostra da _START_ a _END_ di _TOTAL_',
            infoEmpty: 'Nessun record',
            zeroRecords: 'Nessun risultato trovato',
            loadingRecords: 'Caricamento...',
            processing: 'Elaborazione...',
            paginate: {
                first: 'Prima',
                last: 'Ultima',
                next: 'Successiva',
                previous: 'Precedente'
            }
        }
    });
});
</script>

