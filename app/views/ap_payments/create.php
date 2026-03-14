
<div class="card">
  <div class="card-body">
    <h5 class="card-title mb-3">Registra Nuovo Pagamento</h5>
    <form method="post" action="<?php echo \App\Core\Helpers::url('/ap/payments'); ?>">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Socio</label>
          <select name="member_id" class="form-select" required>
            <option value="">Seleziona</option>
            <?php foreach ($members as $m) { ?>
              <option value="<?php echo (int)$m['id']; ?>"><?php echo htmlspecialchars($m['last_name'].' '.$m['first_name']); ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label">Anno</label>
          <input type="number" name="year" class="form-control" value="<?php echo (int)$year; ?>" required>
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label">Data pagamento</label>
          <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label">Importo</label>
          <input type="number" step="0.01" min="0" name="amount" class="form-control" required>
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label">Metodo</label>
          <select name="method" class="form-select">
            <option value="bank">Bonifico</option>
            <option value="cash">Contanti</option>
            <option value="card">Carta</option>
          </select>
        </div>
        <div class="col-md-12 mb-3">
          <label class="form-label">Note</label>
          <input type="text" name="notes" class="form-control">
        </div>
      </div>
      <button class="btn btn-primary">Registra pagamento</button>
      <a href="<?php echo \App\Core\Helpers::url('/memberships?year='.(int)$year); ?>" class="btn btn-secondary">Annulla</a>
    </form>
  </div>
</div>

<div class="card mt-4">
  <div class="card-body">
    <h5 class="card-title mb-3">Ultimi Pagamenti Registrati</h5>
    <div class="table-responsive">
      <table id="datatable" class="table table-striped table-bordered text-nowrap">
        <thead>
          <tr>
            <th>Data</th>
            <th>Socio</th>
            <th>Importo</th>
            <th>Metodo</th>
            <th>Ricevuta</th>
            <th>Azioni</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($recentPayments)) { foreach ($recentPayments as $p) { ?>
          <tr>
            <td data-sort="<?php echo strtotime($p['payment_date']); ?>"><?php echo date('d/m/Y', strtotime($p['payment_date'])); ?></td>
            <td><?php echo htmlspecialchars($p['last_name'].' '.$p['first_name']); ?></td>
            <td>€ <?php echo number_format((float)$p['amount'], 2, ',', '.'); ?></td>
            <td><?php echo htmlspecialchars($p['method']); ?></td>
            <td><?php echo $p['receipt_number'] . '/' . $p['receipt_year']; ?></td>
            <td>
              <a href="<?php echo \App\Core\Helpers::url('/receipts/'.$p['id'].'/download'); ?>" class="btn btn-sm btn-outline-primary me-1" target="_blank" title="Scarica">
                <i class="ti ti-download"></i>
              </a>
              <?php if (!empty($p['document_id'])) { ?>
              <a href="<?php echo \App\Core\Helpers::url('/documents/'.$p['document_id'].'/download'); ?>" class="btn btn-sm btn-outline-info me-1" target="_blank" title="Anteprima">
                  <i class="ti ti-eye"></i>
              </a>
              <form method="post" action="<?php echo \App\Core\Helpers::url('/documents/'.$p['document_id'].'/email'); ?>" class="d-inline" data-confirm="Inviare via email?">
                  <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
                  <button type="submit" class="btn btn-sm btn-outline-warning me-1" title="Invia Email"><i class="ti ti-mail"></i></button>
              </form>
              <?php } else { ?>
                  <!-- Fallback se il documento non è collegato ma esiste (vecchia gestione) -->
                  <a href="<?php echo \App\Core\Helpers::url('/receipts/'.$p['id'].'/download'); ?>" class="btn btn-sm btn-outline-info me-1" target="_blank" title="Anteprima">
                      <i class="ti ti-eye"></i>
                  </a>
              <?php } ?>
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="confirmRegenerate('<?php echo $p['id']; ?>', '<?php echo htmlspecialchars($p['receipt_number']); ?>')" title="Rigenera">
                  <i class="ti ti-refresh"></i>
              </button>
            </td>
          </tr>
          <?php } } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Conferma Rigenerazione -->
<div class="modal fade" id="regenerateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Conferma Rigenerazione</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Sei sicuro di voler rigenerare la ricevuta <strong><span id="receiptNum"></span></strong>?</p>
        <p class="text-danger small">Verrà ricreato il file PDF utilizzando il template HTML corrente. Il file precedente verrà sovrascritto.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
        <form id="regenerateForm" method="post" action="">
            <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
            <button type="submit" class="btn btn-warning">Sì, Rigenera</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function confirmRegenerate(id, number) {
    const modal = new bootstrap.Modal(document.getElementById('regenerateModal'));
    document.getElementById('receiptNum').textContent = number;
    document.getElementById('regenerateForm').action = "<?php echo \App\Core\Helpers::url('/receipts/'); ?>" + id + "/regenerate";
    modal.show();
}

document.addEventListener('DOMContentLoaded', function(){
    if (!window.jQuery) return;
    var $ = window.jQuery;
    $('#datatable').DataTable({
        responsive: true,
        order: [[0, 'desc']], 
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/it-IT.json'
        }
    });
});
</script>
