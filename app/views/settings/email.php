<?php
use App\Core\Helpers;
$row = $data['row'] ?? [];
?>

<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="card-title mb-0">Impostazioni Email</h4>
        <a href="<?php echo Helpers::url('/settings'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-arrow-left"></i> Torna a Settings
        </a>
    </div>

    <div class="alert alert-info">
        Configura i parametri SMTP per l'invio delle email dal sistema.
    </div>

    <hr class="my-4">

    <form method="post" action="<?php echo Helpers::url('/settings/email/update'); ?>">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      
      <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Host SMTP</label>
            <input type="text" name="smtp_host" class="form-control" placeholder="es. smtp.gmail.com" value="<?php echo htmlspecialchars($row['smtp_host'] ?? ''); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Porta SMTP</label>
            <input type="number" name="smtp_port" class="form-control" placeholder="es. 587" value="<?php echo htmlspecialchars($row['smtp_port'] ?? '587'); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Sicurezza</label>
            <select name="smtp_secure" class="form-select">
                <option value="tls" <?php echo ($row['smtp_secure'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                <option value="ssl" <?php echo ($row['smtp_secure'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                <option value="" <?php echo ($row['smtp_secure'] ?? '') === '' ? 'selected' : ''; ?>>Nessuna</option>
            </select>
          </div>
          
          <div class="col-md-6">
            <label class="form-label">Username SMTP</label>
            <input type="text" name="username" class="form-control" placeholder="es. tuaemail@gmail.com" value="<?php echo htmlspecialchars($row['username'] ?? ''); ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Password SMTP</label>
            <input type="password" name="password" class="form-control" placeholder="Lasciare vuoto per non modificare" value="<?php echo htmlspecialchars($row['password'] ?? ''); ?>">
          </div>
          
          <div class="col-md-6">
            <label class="form-label">Email Mittente (From)</label>
            <input type="email" name="smtp_from_email" class="form-control" placeholder="es. no-reply@tuosito.it" value="<?php echo htmlspecialchars($row['smtp_from_email'] ?? ''); ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Nome Mittente</label>
            <input type="text" name="smtp_from_name" class="form-control" placeholder="es. Associazione AP" value="<?php echo htmlspecialchars($row['smtp_from_name'] ?? ''); ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">CC (separati da virgola)</label>
            <input type="text" name="smtp_cc" class="form-control" value="<?php echo htmlspecialchars($row['smtp_cc'] ?? ''); ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">BCC (separati da virgola)</label>
            <input type="text" name="smtp_bcc" class="form-control" value="<?php echo htmlspecialchars($row['smtp_bcc'] ?? ''); ?>">
          </div>
      </div>

      <hr class="my-4">
      <h5 class="mb-3">Testi Email Automatiche</h5>
      
      <ul class="nav nav-tabs mb-3" id="emailTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="cert-tab" data-bs-toggle="tab" data-bs-target="#cert" type="button" role="tab">Certificato Iscrizione</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="dm-tab" data-bs-toggle="tab" data-bs-target="#dm" type="button" role="tab">Attestato Corso</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="rec-tab" data-bs-toggle="tab" data-bs-target="#rec" type="button" role="tab">Ricevuta</button>
        </li>
      </ul>
      
      <div class="tab-content" id="emailTabsContent">
        <div class="tab-pane fade show active" id="cert" role="tabpanel">
            <div class="mb-3">
                <label class="form-label">Oggetto Email</label>
                <input type="text" name="email_certificate_subject" class="form-control" value="<?php echo htmlspecialchars($row['email_certificate_subject'] ?? 'Certificato di Iscrizione'); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Corpo Email</label>
                <textarea name="email_certificate_body" class="form-control" rows="5"><?php echo htmlspecialchars($row['email_certificate_body'] ?? "Gentile {{NOME}},\n\nin allegato trovi il tuo certificato di iscrizione per l'anno {{ANNO}}.\n\nCordiali saluti."); ?></textarea>
                <div class="form-text">Placeholder: {{NOME}}, {{ANNO}}</div>
            </div>
        </div>
        <div class="tab-pane fade" id="dm" role="tabpanel">
            <div class="mb-3">
                <label class="form-label">Oggetto Email</label>
                <input type="text" name="email_dm_certificate_subject" class="form-control" value="<?php echo htmlspecialchars($row['email_dm_certificate_subject'] ?? 'Attestato di Partecipazione'); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Corpo Email</label>
                <textarea name="email_dm_certificate_body" class="form-control" rows="5"><?php echo htmlspecialchars($row['email_dm_certificate_body'] ?? "Gentile {{NOME}},\n\nin allegato trovi il tuo attestato di partecipazione.\n\nCordiali saluti."); ?></textarea>
                <div class="form-text">Placeholder: {{NOME}}</div>
            </div>
        </div>
        <div class="tab-pane fade" id="rec" role="tabpanel">
            <div class="mb-3">
                <label class="form-label">Oggetto Email</label>
                <input type="text" name="email_receipt_subject" class="form-control" value="<?php echo htmlspecialchars($row['email_receipt_subject'] ?? 'Ricevuta Pagamento'); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Corpo Email</label>
                <textarea name="email_receipt_body" class="form-control" rows="5"><?php echo htmlspecialchars($row['email_receipt_body'] ?? "Gentile {{NOME}},\n\nin allegato trovi la ricevuta del tuo pagamento.\n\nCordiali saluti."); ?></textarea>
                <div class="form-text">Placeholder: {{NOME}}, {{ANNO}}</div>
            </div>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">Salva Configurazione</button>
        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#testEmailModal">Test Invio Email</button>
      </div>
    </form>
    
    <!-- Modal Test Email -->
    <div class="modal fade" id="testEmailModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Test Invio Email</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="post" action="<?php echo Helpers::url('/settings/email/test'); ?>">
              <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
              <div class="modal-body">
                <p>Invia un'email di prova per verificare la connessione SMTP.</p>
                <div class="mb-3">
                    <label class="form-label">Email Destinatario</label>
                    <input type="email" name="test_email" class="form-control" required placeholder="tuoindirizzo@esempio.com">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="submit" class="btn btn-primary">Invia Test</button>
              </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
