<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Scheda Socio</h3>
  <div>
    <a href="<?php echo \App\Core\Helpers::url('/members/'.$row['id'].'/edit'); ?>" class="btn btn-outline-primary">Modifica</a>
    <form method="post" action="<?php echo \App\Core\Helpers::url('/members/'.$row['id'].'/delete'); ?>" class="d-inline">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      <button class="btn btn-outline-danger" onclick="return confirm('Disattivare il socio?')">Disattiva</button>
    </form>
  </div>
</div>
<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <h5 class="mb-3">Dati Personali e Studio</h5>
        <p><strong>N. Socio AP:</strong> <?php echo htmlspecialchars($row['member_number'] ?? '-'); ?></p>
        <p><strong>Nome:</strong> <?php echo htmlspecialchars($row['first_name']); ?></p>
        <p><strong>Cognome:</strong> <?php echo htmlspecialchars($row['last_name']); ?></p>
        <p><strong>Nome Studio:</strong> <?php echo htmlspecialchars($row['studio_name'] ?? '-'); ?></p>
        <p><strong>Codice Fiscale:</strong> <?php echo htmlspecialchars($row['tax_code'] ?? '-'); ?></p>
        <p><strong>CF/PIVA Fattura:</strong> <?php echo htmlspecialchars($row['billing_cf_piva'] ?? '-'); ?></p>
        <p><strong>Revisore (54h):</strong> <?php echo ($row['is_revisor'] ?? 0) ? 'Sì' : 'No'; ?></p>
        <p><strong>N. Revisione:</strong> <?php echo htmlspecialchars($row['revision_number'] ?? '-'); ?></p>
      </div>
      <div class="col-md-6">
        <h5 class="mb-3">Contatti e Stato</h5>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email'] ?? '-'); ?></p>
        <p><strong>Cellulare:</strong> <?php echo htmlspecialchars($row['mobile_phone'] ?? '-'); ?></p>
        <p><strong>Telefono Fisso:</strong> <?php echo htmlspecialchars($row['phone'] ?? '-'); ?></p>
        <p><strong>Indirizzo:</strong> <?php echo htmlspecialchars($row['address'] ?? '-'); ?> - <?php echo htmlspecialchars($row['zip_code'] ?? ''); ?> <?php echo htmlspecialchars($row['city'] ?? '-'); ?> (<?php echo htmlspecialchars($row['province'] ?? ''); ?>)</p>
        <p><strong>Data di nascita:</strong> <?php echo !empty($row['birth_date']) ? date('d/m/Y', strtotime($row['birth_date'])) : '-'; ?></p>
        <p><strong>Data Iscrizione:</strong> <?php echo !empty($row['registration_date']) ? date('d/m/Y', strtotime($row['registration_date'])) : '-'; ?></p>
        <p><strong>Stato:</strong> <?php echo htmlspecialchars($row['status']); ?></p>
      </div>
    </div>
  </div>
</div>

<!-- Sezione Iscrizioni e Rinnovi -->
<div class="card mt-3">
  <div class="card-body">
    <h5 class="mb-3">Storico Iscrizioni e Rinnovi</h5>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
            <tr>
                <th>Anno</th>
                <th>Stato Iscrizione</th>
                <th>Data Rinnovo</th>
                <th>Data Pagamento</th>
                <th>Importo</th>
            </tr>
        </thead>
        <tbody>
          <?php 
          $statusMap = [
              'active' => 'Attivo', 'inactive' => 'Inattivo', 'suspended' => 'Sospeso', 'expelled' => 'Espulso', 'deceased' => 'Deceduto',
              'regular' => 'Regolare', 'pending' => 'In attesa', 'expired' => 'Scaduto'
          ];
          if (!empty($renewals)) { foreach ($renewals as $ren) { ?>
          <tr>
            <td><a href="<?php echo \App\Core\Helpers::url('/renewals/'.$ren['id'].'/edit'); ?>"><?php echo (int)$ren['year']; ?></a></td>
            <td><?php echo htmlspecialchars($statusMap[$ren['status']] ?? $ren['status']); ?></td>
            <td><?php echo !empty($ren['renewal_date']) ? date('d/m/Y', strtotime($ren['renewal_date'])) : '-'; ?></td>
            <td><?php echo !empty($ren['payment_date']) ? date('d/m/Y', strtotime($ren['payment_date'])) : '-'; ?></td>
            <td><?php echo isset($ren['amount']) ? '€ ' . number_format($ren['amount'], 2, ',', '.') : '-'; ?></td>
          </tr>
          <?php } } else { ?>
          <tr><td colspan="5" class="text-center text-muted">Nessuna iscrizione registrata</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Sezione Corsi e Attestati -->
<div class="card mt-3">
  <div class="card-body">
    <h5 class="mb-3">Corsi Frequentati</h5>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
            <tr>
                <th>Data Corso</th>
                <th>Titolo</th>
                <th>Stato</th>
                <th>Attestato</th>
            </tr>
        </thead>
        <tbody>
          <?php if (!empty($courses)) { foreach ($courses as $c) { ?>
          <tr>
            <td><?php echo htmlspecialchars($c['course_date']); ?></td>
            <td><?php echo htmlspecialchars($c['title']); ?></td>
            <td><span class="badge bg-success">Partecipato</span></td>
            <td>
                <?php if (!empty($c['certificate_path'])) { ?>
                    <span class="text-success"><i class="ti ti-check"></i> Rilasciato</span>
                    <a href="<?php echo \App\Core\Helpers::url('/documents/'.$c['certificate_document_id'].'/download'); ?>" class="btn btn-sm btn-outline-primary ms-2">Scarica</a>
                <?php } else { ?>
                    <span class="text-warning">Non rilasciato</span>
                <?php } ?>
            </td>
          </tr>
          <?php } } else { ?>
          <tr><td colspan="4" class="text-center text-muted">Nessun corso frequentato</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
  $docsModel = new \App\Models\Document();
  $docs = $docsModel->byMember((int)$row['id']);
?>
<div class="card mt-3">
  <div class="card-body">
    <h5 class="mb-3">Documenti</h5>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Tipo</th><th>Anno</th><th>File</th><th>Generato il</th><th>Azioni</th></tr></thead>
        <tbody>
          <?php foreach ($docs as $d) { 
              $typeLabel = $d['type'];
              if ($typeLabel === 'receipt') $typeLabel = 'Ricevuta';
              elseif ($typeLabel === 'membership_certificate') $typeLabel = 'Certificato Iscrizione';
              elseif ($typeLabel === 'course_certificate') $typeLabel = 'Attestato Corso';
              
              $fileExt = strtolower(pathinfo($d['file_path'], PATHINFO_EXTENSION));
          ?>
          <tr>
            <td><?php echo htmlspecialchars($typeLabel); ?></td>
            <td><?php echo (int)$d['year']; ?></td>
            <td><?php echo htmlspecialchars(basename($d['file_path'])); ?></td>
            <td><?php echo !empty($d['created_at']) ? date('d/m/Y', strtotime($d['created_at'])) : '-'; ?></td>
            <td class="text-end">
              <?php if ($fileExt === 'pdf') { ?>
              <a class="btn btn-sm btn-outline-info me-1" href="<?php echo \App\Core\Helpers::url('/documents/'.$d['id'].'/download'); ?>" target="_blank" title="Anteprima">
                  <i class="ti ti-eye"></i>
              </a>
              <?php } ?>
              <a class="btn btn-sm btn-outline-primary me-1" href="<?php echo \App\Core\Helpers::url('/documents/'.$d['id'].'/download?download=1'); ?>" title="Scarica">
                  <i class="ti ti-download"></i>
              </a>
              <form method="post" action="<?php echo \App\Core\Helpers::url('/documents/'.$d['id'].'/delete'); ?>" class="d-inline" onsubmit="return confirm('Eliminare questo documento?');">
                  <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Elimina"><i class="ti ti-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php } ?>
          <?php if (empty($docs)) { ?>
          <tr><td colspan="4" class="text-center text-muted">Nessun documento</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer">
    <a href="<?php echo \App\Core\Helpers::url('/ap/payments/create'); ?>" class="btn btn-primary">Genera ricevuta (nuovo pagamento)</a>
  </div>
</div>

<div class="card mt-3">
  <div class="card-body">
    <h5 class="mb-3">Certificato di appartenenza</h5>
    <form method="post" action="<?php echo \App\Core\Helpers::url('/documents/membership-certificate/generate'); ?>" class="row g-2">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      <input type="hidden" name="member_id" value="<?php echo (int)$row['id']; ?>">
      <div class="col-sm-3">
        <input type="number" name="year" class="form-control" value="<?php echo (int)date('Y'); ?>">
      </div>
      <div class="col-sm-3">
        <button class="btn btn-secondary">Genera certificato</button>
      </div>
    </form>
  </div>
 </div>
