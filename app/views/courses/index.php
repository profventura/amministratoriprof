<div class="row mb-3">
  <div class="col-md-6">
    <div class="card bg-primary-subtle">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-center round-48 rounded text-bg-primary flex-shrink-0 mb-3 mx-auto">
          <iconify-icon icon="solar:calendar-line-duotone" class="icon-24 text-white"></iconify-icon>
        </div>
        <h5 class="card-title fw-semibold text-center mb-1">Totale Corsi</h5>
        <h2 class="card-text text-primary text-center"><?php echo count($rows); ?></h2>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card bg-success-subtle">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-center round-48 rounded text-bg-success flex-shrink-0 mb-3 mx-auto">
          <iconify-icon icon="solar:calendar-add-line-duotone" class="icon-24 text-white"></iconify-icon>
        </div>
        <h5 class="card-title fw-semibold text-center mb-1">Corsi Quest'Anno</h5>
        <?php $thisYear = array_filter($rows, fn($r) => (int)$r['year'] === (int)date('Y')); ?>
        <h2 class="card-text text-success text-center"><?php echo count($thisYear); ?></h2>
      </div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Elenco Corsi</h3>
  <a href="<?php echo \App\Core\Helpers::url('/courses/create'); ?>" class="btn btn-primary">Nuovo Corso</a>
</div>

<div class="table-responsive">
  <table id="datatable" class="table table-striped table-bordered text-nowrap">
    <thead class="table-light">
      <tr>
        <th>Titolo</th>
        <th>Data</th>
        <th>Orario</th>
        <th>Anno</th>
        <th>Luogo</th>
        <th>Ore</th>
        <th>Azioni</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r) { ?>
      <tr>
        <td><?php echo htmlspecialchars($r['title']); ?></td>
        <td><?php echo date('d/m/Y', strtotime($r['course_date'])); ?></td>
        <td><?php echo htmlspecialchars(($r['start_time'] ?? '').' - '.($r['end_time'] ?? '')); ?></td>
        <td><?php echo (int)$r['year']; ?></td>
        <td><?php echo htmlspecialchars($r['location'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($r['hours'] ?? ''); ?></td>
        <td class="text-end">
          <a href="<?php echo \App\Core\Helpers::url('/courses/'.$r['id']); ?>" class="btn btn-sm btn-outline-primary">Apri</a>
          <a href="<?php echo \App\Core\Helpers::url('/courses/'.$r['id'].'/edit'); ?>" class="btn btn-sm btn-outline-warning">Modifica</a>
          <form action="<?php echo \App\Core\Helpers::url('/courses/' . $r['id'] . '/delete'); ?>" method="post" class="d-inline delete-form">
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
        <p>Sei sicuro di voler eliminare questo corso?</p>
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
    
    // Gestione eliminazione con modale
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    var formToSubmit = null;

    $(document).on('submit', 'form.delete-form', function(e){
        e.preventDefault();
        formToSubmit = this;
        deleteModal.show();
    });

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
        order: [[1, 'desc']], // Ordina per Data decrescente di default
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
