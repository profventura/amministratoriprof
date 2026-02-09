<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-4">Modifica Iscrizione <?php echo (int)$row['year']; ?></h4>
        <h5 class="mb-4 text-primary"><?php echo htmlspecialchars($row['last_name'] . ' ' . $row['first_name']); ?></h5>
        
        <form action="<?php echo \App\Core\Helpers::url('/memberships/'.$row['id'].'/edit'); ?>" method="post">
          <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
          
          <div class="mb-3">
            <label class="form-label">Stato Iscrizione</label>
            <select name="status" class="form-select">
              <option value="pending" <?php echo $row['status']==='pending'?'selected':''; ?>>In Attesa (Pending)</option>
              <option value="regular" <?php echo $row['status']==='regular'?'selected':''; ?>>In Regola (Regular)</option>
              <option value="overdue" <?php echo $row['status']==='overdue'?'selected':''; ?>>Scaduto (Overdue)</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Data Rinnovo</label>
            <input type="date" name="renewal_date" class="form-control" value="<?php echo htmlspecialchars($row['renewal_date'] ?? ''); ?>">
            <div class="form-text">Lasciare vuoto se non ancora rinnovato.</div>
          </div>
          
          <div class="d-flex justify-content-end mt-4">
            <a href="<?php echo \App\Core\Helpers::url('/memberships?year='.$row['year']); ?>" class="btn btn-outline-secondary me-2">Annulla</a>
            <button type="submit" class="btn btn-primary">Salva Modifiche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>