<div class="row mb-3">
  <div class="col-md-6">
    <div class="card bg-primary-subtle">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-center round-48 rounded text-bg-primary flex-shrink-0 mb-3 mx-auto">
          <iconify-icon icon="solar:bill-list-linear" class="icon-24 text-white"></iconify-icon>
        </div>
        <h5 class="card-title fw-semibold text-center mb-1">Totale Ricevute <?php echo $year; ?></h5>
        <h2 class="card-text text-primary text-center"><?php echo count($rows); ?></h2>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card bg-success-subtle">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-center round-48 rounded text-bg-success flex-shrink-0 mb-3 mx-auto">
          <iconify-icon icon="solar:wallet-money-line-duotone" class="icon-24 text-white"></iconify-icon>
        </div>
        <h5 class="card-title fw-semibold text-center mb-1">Totale Incassato</h5>
        <?php $totalAmount = array_sum(array_column($rows, 'amount')); ?>
        <h2 class="card-text text-success text-center">€ <?php echo number_format($totalAmount, 2, ',', '.'); ?></h2>
      </div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Elenco Ricevute</h3>
  <form class="d-flex" method="get" action="<?php echo \App\Core\Helpers::url('/receipts'); ?>">
    <input type="number" class="form-control me-2" name="year" value="<?php echo (int)$year; ?>" style="width: 100px;">
    <button class="btn btn-outline-primary">Cambia Anno</button>
  </form>
</div>

<div class="table-responsive">
  <table id="datatable" class="table table-striped table-bordered text-nowrap">
    <thead class="table-light">
      <tr>
        <th>N. Ricevuta</th>
        <th>Data</th>
        <th>Socio</th>
        <th>Importo</th>
        <th>Metodo</th>
        <th>Azioni</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r) { ?>
      <tr>
        <td><?php echo htmlspecialchars($r['receipt_number']); ?></td>
        <td><?php echo htmlspecialchars($r['payment_date']); ?></td>
        <td><?php echo htmlspecialchars($r['last_name'].' '.$r['first_name']); ?></td>
        <td>€ <?php echo number_format((float)$r['amount'], 2, ',', '.'); ?></td>
        <td><?php echo htmlspecialchars($r['method']); ?></td>
        <td>
          <a class="btn btn-sm btn-outline-primary" href="<?php echo \App\Core\Helpers::url('/receipts/'.$r['id'].'/download'); ?>">Scarica</a>
          <form method="post" action="<?php echo \App\Core\Helpers::url('/receipts/'.$r['id'].'/regenerate'); ?>" class="d-inline" onsubmit="return confirm('Rigenerare il PDF della ricevuta?');">
            <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
            <button class="btn btn-sm btn-outline-warning ms-1">Rigenera</button>
          </form>
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

