<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Ricevute <?php echo (int)$year; ?></h3>
  <form class="d-flex" method="get" action="<?php echo \App\Core\Helpers::url('/receipts'); ?>">
    <input type="number" class="form-control me-2" name="year" value="<?php echo (int)$year; ?>">
    <button class="btn btn-secondary">Vai</button>
  </form>
</div>
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table id="datatable" class="table mb-0">
        <thead><tr><th>N. ricevuta</th><th>Data</th><th>Socio</th><th>Importo</th><th>Metodo</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r) { ?>
          <tr>
            <td><?php echo htmlspecialchars($r['receipt_number']); ?></td>
            <td><?php echo htmlspecialchars($r['payment_date']); ?></td>
            <td><?php echo htmlspecialchars($r['last_name'].' '.$r['first_name']); ?></td>
            <td>€ <?php echo number_format((float)$r['amount'], 2, ',', '.'); ?></td>
            <td><?php echo htmlspecialchars($r['method']); ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="<?php echo \App\Core\Helpers::url('/receipts/'.$r['id'].'/download'); ?>">Scarica</a>
              <form method="post" action="<?php echo \App\Core\Helpers::url('/receipts/'.$r['id'].'/regenerate'); ?>" class="d-inline">
                <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
                <button class="btn btn-sm btn-outline-secondary">Rigenera</button>
              </form>
            </td>
          </tr>
          <?php } ?>
          <?php if (empty($rows)) { ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Nessuna ricevuta per l'anno selezionato</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
$(document).ready(function() {
    $('#datatable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excelHtml5', className: 'btn btn-success btn-sm', text: 'Excel' },
            { extend: 'pdfHtml5', className: 'btn btn-danger btn-sm', text: 'PDF' }
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/it-IT.json' },
        paging: true,
        ordering: true,
        info: true
    });
});
</script>

