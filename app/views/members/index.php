<div class="row mb-3">
  <div class="col-md-4">
    <div class="card bg-primary-subtle">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-center round-48 rounded text-bg-primary flex-shrink-0 mb-3 mx-auto">
          <iconify-icon icon="solar:users-group-rounded-line-duotone" class="icon-24 text-white"></iconify-icon>
        </div>
        <h5 class="card-title fw-semibold text-center mb-1">Totale Soci</h5>
        <h2 class="card-text text-primary text-center"><?php echo $stats['total']; ?></h2>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card bg-success-subtle">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-center round-48 rounded text-bg-success flex-shrink-0 mb-3 mx-auto">
          <iconify-icon icon="solar:user-check-rounded-line-duotone" class="icon-24 text-white"></iconify-icon>
        </div>
        <h5 class="card-title fw-semibold text-center mb-1">Soci Attivi</h5>
        <h2 class="card-text text-success text-center"><?php echo $stats['active']; ?></h2>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card bg-warning-subtle">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-center round-48 rounded text-bg-warning flex-shrink-0 mb-3 mx-auto">
          <iconify-icon icon="solar:user-plus-rounded-line-duotone" class="icon-24 text-white"></iconify-icon>
        </div>
        <h5 class="card-title fw-semibold text-center mb-1">Nuovi Quest'Anno</h5>
        <h2 class="card-text text-warning text-center"><?php echo $stats['new_this_year']; ?></h2>
      </div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-end mb-3 gap-2">
  <a class="btn btn-primary" href="<?php echo \App\Core\Helpers::url('/members/create'); ?>">Nuovo Socio</a>
</div>

<div class="table-responsive">
  <table id="datatable" class="table table-striped table-bordered text-nowrap">
    <thead class="table-light">
      <tr>
        <th>N. Socio</th>
        <th>Cognome</th>
        <th>Nome</th>
        <th>Studio</th>
        <th>Email</th>
        <th>Città</th>
        <th>Stato</th>
        <th>Azioni</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r) { ?>
      <tr>
        <td><?php echo htmlspecialchars($r['member_number'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($r['last_name']); ?></td>
        <td><?php echo htmlspecialchars($r['first_name']); ?></td>
        <td><?php echo htmlspecialchars($r['studio_name'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($r['email']); ?></td>
        <td><?php echo htmlspecialchars($r['city']); ?></td>
        <td>
            <?php if ($r['status'] === 'active') { ?>
                <span class="badge bg-success">Attivo</span>
            <?php } else { ?>
                <span class="badge bg-secondary">Inattivo</span>
            <?php } ?>
        </td>
        <td>
          <a href="<?php echo \App\Core\Helpers::url('/members/'.$r['id']); ?>" class="btn btn-sm btn-outline-primary">Apri</a>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    if (!window.jQuery) return;
    var $ = window.jQuery;
    $('#datatable').DataTable({
        responsive: true,
        deferRender: true,
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, "All"]],
        order: [[1, 'asc']], // Ordina per Cognome di default
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

