<div class="row justify-content-center">
  <div class="col-lg-10">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-4">Configurazione SMTP (Server Email)</h4>
        
        <form action="<?php echo \App\Core\Helpers::url('/settings/email/update'); ?>" method="post">
          <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
          
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">SMTP Host</label>
              <input type="text" name="smtp_host" class="form-control" value="<?php echo htmlspecialchars($row['smtp_host'] ?? ''); ?>" placeholder="es. smtp.gmail.com">
            </div>
            <div class="col-md-4">
              <label class="form-label">SMTP Port</label>
              <input type="number" name="smtp_port" class="form-control" value="<?php echo htmlspecialchars($row['smtp_port'] ?? '587'); ?>">
            </div>
            
            <div class="col-md-4">
              <label class="form-label">Sicurezza</label>
              <select name="smtp_secure" class="form-select">
                <option value="none" <?php echo ($row['smtp_secure']??'')=='none'?'selected':''; ?>>Nessuna</option>
                <option value="tls" <?php echo ($row['smtp_secure']??'')=='tls'?'selected':''; ?>>TLS</option>
                <option value="ssl" <?php echo ($row['smtp_secure']??'')=='ssl'?'selected':''; ?>>SSL</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Username SMTP</label>
              <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($row['username'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Password SMTP</label>
              <input type="password" name="password" class="form-control" value="<?php echo htmlspecialchars($row['password'] ?? ''); ?>">
            </div>
            
            <div class="col-12"><hr class="my-3"></div>
            <h5 class="mb-3">Mittente e Copie</h5>
            
            <div class="col-md-6">
              <label class="form-label">Email Mittente (Da)</label>
              <input type="email" name="smtp_from_email" class="form-control" value="<?php echo htmlspecialchars($row['smtp_from_email'] ?? ''); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nome Mittente</label>
              <input type="text" name="smtp_from_name" class="form-control" value="<?php echo htmlspecialchars($row['smtp_from_name'] ?? ''); ?>" required>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">CC (Copia Conoscenza)</label>
              <input type="text" name="smtp_cc" class="form-control" value="<?php echo htmlspecialchars($row['smtp_cc'] ?? ''); ?>" placeholder="email1@test.it, email2@test.it">
              <div class="form-text">Separa più email con una virgola.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">CCN (Copia Nascosta)</label>
              <input type="text" name="smtp_bcc" class="form-control" value="<?php echo htmlspecialchars($row['smtp_bcc'] ?? ''); ?>" placeholder="email1@test.it, email2@test.it">
            </div>
          </div>

          <div class="col-12"><hr class="my-4"></div>
          
          <h4 class="card-title mb-4">Template Testi Email</h4>
          
          <div class="mb-4 p-3 bg-light border rounded">
             <h6 class="text-primary mb-2">Placeholder Disponibili:</h6>
             <ul class="mb-0 small text-muted columns-2">
                 <li><code>{NOME}</code> - Nome Socio</li>
                 <li><code>{COGNOME}</code> - Cognome Socio</li>
                 <li><code>{ANNO}</code> - Anno di riferimento</li>
                 <li><code>{NUMERO}</code> - Numero Tessera/Protocollo</li>
                 <li><code>{SCADENZA}</code> - Data Scadenza (se applicabile)</li>
                 <li><code>{CORSO}</code> - Titolo Corso (solo per Attestati)</li>
                 <li><code>{DATA_CORSO}</code> - Data Corso (solo per Attestati)</li>
             </ul>
          </div>

          <!-- Email Certificato Iscrizione -->
          <h5 class="mb-3">Email per Certificato Iscrizione (Soci)</h5>
          <div class="mb-3">
            <label class="form-label">Oggetto</label>
            <input type="text" name="email_certificate_subject" class="form-control" value="<?php echo htmlspecialchars($row['email_certificate_subject'] ?? 'Invio Certificato Iscrizione'); ?>">
          </div>
          <div class="mb-4">
            <label class="form-label">Corpo del messaggio</label>
            <textarea name="email_certificate_body" class="form-control" rows="6"><?php echo htmlspecialchars($row['email_certificate_body'] ?? "Gentile {NOME} {COGNOME},\n\nin allegato trovi il certificato di iscrizione per l'anno {ANNO}.\n\nCordiali saluti,\nL'Associazione"); ?></textarea>
          </div>

          <!-- Email Attestato DM (Corsi) -->
          <h5 class="mb-3 pt-3 border-top">Email per Attestato Partecipazione (Corsi)</h5>
          <div class="mb-3">
            <label class="form-label">Oggetto</label>
            <input type="text" name="email_dm_certificate_subject" class="form-control" value="<?php echo htmlspecialchars($row['email_dm_certificate_subject'] ?? 'Invio Attestato Partecipazione'); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Corpo del messaggio</label>
            <textarea name="email_dm_certificate_body" class="form-control" rows="6"><?php echo htmlspecialchars($row['email_dm_certificate_body'] ?? "Gentile {NOME} {COGNOME},\n\nin allegato trovi l'attestato di partecipazione al corso \"{CORSO}\" tenutosi il {DATA_CORSO}.\n\nCordiali saluti,\nL'Associazione"); ?></textarea>
          </div>

          <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary px-4">Salva Impostazioni Email</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>