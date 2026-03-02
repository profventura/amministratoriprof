<?php
use App\Core\Helpers;
$row = $data['row'] ?? [];
$htmlContent = $data['htmlContent'] ?? '';
?>

<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="card-title mb-0">Gestione Template Ricevute (HTML)</h4>
        <div>
             <a href="<?php echo Helpers::url('/settings'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-arrow-left"></i> Torna a Settings
             </a>
        </div>
    </div>

    <div class="alert alert-info">
        Modifica il template HTML per le ricevute. Usa i placeholder elencati sotto per inserire i dati dinamici.
    </div>

    <hr class="my-4">
    
    <form method="post" action="<?php echo Helpers::url('/settings/ricevute/update-template'); ?>" id="htmlForm">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      
      <div class="row">
          <div class="col-12" id="code-col">
              <textarea name="html_content" id="html_content" class="form-control" rows="25"><?php echo htmlspecialchars($htmlContent); ?></textarea>
          </div>
      </div>

      <div class="card bg-light border-0 mb-4 mt-3">
          <div class="card-body">
              <h6 class="text-primary mb-2">Placeholder Disponibili</h6>
              <div class="row g-2 text-muted small">
                  <div class="col-md-4"><code>{{receipt_number}}</code> - Numero Ricevuta</div>
                  <div class="col-md-4"><code>{{year}}</code> - Anno Corrente</div>
                  <div class="col-md-4"><code>{{receipt_date}}</code> - Data Ricevuta</div>
                  <div class="col-md-4"><code>{{billing_details}}</code> - Dati Intestatario (HTML)</div>
                  <div class="col-md-4"><code>{{description}}</code> - Oggetto Prestazione</div>
                  <div class="col-md-4"><code>{{item_description}}</code> - Descrizione Riga Tabella</div>
                  <div class="col-md-4"><code>{{amount}}</code> - Importo Totale</div>
                  <div class="col-md-4"><code>{{payment_date}}</code> - Data Pagamento</div>
              </div>
          </div>
      </div>

      <div class="d-flex gap-2 align-items-center">
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy"></i> Salva Template
        </button>
        <button type="button" class="btn btn-outline-dark" onclick="previewHtml()">
            <i class="ti ti-eye"></i> Anteprima HTML
        </button>
        <button type="button" class="btn btn-outline-danger" onclick="previewTemplatePdf()">
            <i class="ti ti-file-text"></i> Anteprima PDF
        </button>
      </div>
    </form>
    
    <hr class="my-5">
    
    <h4 class="mb-3">Creazione Manuale Ricevuta</h4>
    <div class="alert alert-warning">
        <i class="ti ti-alert-triangle"></i> 
        Le ricevute create manualmente verranno salvate nel database e appariranno nella lista. 
        <strong>Nota:</strong> Se rigeneri la ricevuta dalla lista, i testi manuali potrebbero essere sovrascritti dai dati del socio.
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="post" action="<?php echo Helpers::url('/settings/ricevute/create-manual'); ?>" id="manualForm">
                <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Socio Collegato <span class="text-danger">*</span></label>
                        <select name="member_id" class="form-select" required onchange="fillMemberData(this)">
                            <option value="">-- Seleziona Socio --</option>
                            <?php foreach ($members ?? [] as $m): ?>
                                <option value="<?php echo $m['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($m['first_name'] . ' ' . $m['last_name']); ?>"
                                    data-address="<?php echo htmlspecialchars(($m['address'] ?? '') . "\n" . ($m['zip_code'] ?? '') . ' ' . ($m['city'] ?? '') . ' ' . ($m['province'] ?? '')); ?>"
                                    data-cf="<?php echo htmlspecialchars($m['billing_cf'] ?: $m['tax_code']); ?>"
                                    data-piva="<?php echo htmlspecialchars($m['billing_piva']); ?>">
                                    <?php echo htmlspecialchars($m['last_name'] . ' ' . $m['first_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Anno</label>
                        <input type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Data Emissione</label>
                        <input type="text" name="date" class="form-control" value="<?php echo date('d/m/Y'); ?>" required placeholder="dd/mm/yyyy">
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label">Dati Intestatario (HTML/Testo) - <code>{{billing_details}}</code></label>
                        <textarea name="billing_details" id="billing_details" class="form-control" rows="4" required></textarea>
                        <div class="form-text">Puoi usare tag HTML come &lt;strong&gt;, &lt;br&gt;</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Oggetto Prestazione - <code>{{description}}</code></label>
                        <input type="text" name="description" class="form-control" value="QUOTA ANNUALE ISCRIZIONE" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Descrizione Riga - <code>{{item_description}}</code></label>
                        <input type="text" name="item_description" id="item_description" class="form-control" value="QUOTA ANNUALE ASSOCIAZIONE" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Importo (€) - <code>{{amount}}</code></label>
                        <input type="text" name="amount" class="form-control" value="0,00" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Data Pagamento - <code>{{payment_date}}</code></label>
                        <input type="text" name="payment_date" class="form-control" value="<?php echo date('d/m/Y'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Metodo Pagamento</label>
                        <select name="method" class="form-select" required>
                            <option value="bank">Bonifico</option>
                            <option value="cash">Contanti</option>
                            <option value="card">Carta</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-4 d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-outline-dark" onclick="previewManualPdf()">
                        <i class="ti ti-file-text"></i> Anteprima PDF
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-plus"></i> Genera e Registra
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.7/tinymce.min.js" referrerpolicy="no-referrer"></script>
    <script>
    function fillMemberData(select) {
        const opt = select.options[select.selectedIndex];
        if (!opt.value) return;
        
        const name = opt.getAttribute('data-name');
        const address = opt.getAttribute('data-address');
        const cf = opt.getAttribute('data-cf');
        const piva = opt.getAttribute('data-piva');
        
        let html = `<strong>${name}</strong><br>${address.replace(/\n/g, '<br>')}<br>`;
        if (cf) html += `C.F.: ${cf}<br>`;
        if (piva) html += `P.IVA: ${piva}`;
        
        document.getElementById('billing_details').value = html;
        document.getElementById('item_description').value = `QUOTA ANNUALE ASSOCIAZIONE - ${name}`;
    }

    function previewManualPdf() {
        const form = document.getElementById('manualForm');
        const originalAction = form.action;
        form.action = "<?php echo \App\Core\Helpers::url('/settings/ricevute/preview-pdf'); ?>";
        form.target = "_blank";
        form.submit();
        form.action = originalAction;
        form.target = "_self";
    }

    function previewHtml() {
        const form = document.getElementById('htmlForm');
        const originalAction = form.action;
        form.action = "<?php echo \App\Core\Helpers::url('/settings/ricevute/preview-html'); ?>";
        form.target = "_blank";
        form.submit();
        form.action = originalAction;
        form.target = "_self";
    }

    function previewTemplatePdf() {
        const form = document.getElementById('htmlForm');
        const originalAction = form.action;
        // Usiamo previewManualReceiptPdf ma passando i dati finti via hidden fields o lasciando che il controller gestisca i default
        // Tuttavia previewManualReceiptPdf si aspetta campi specifici (amount, date, etc.) che non sono nel form htmlForm (che ha solo html_content)
        // Dobbiamo creare una nuova route o adattare previewManualReceiptPdf per accettare html_content custom
        
        // Creiamo input hidden temporanei per simulare i dati
        const container = document.createElement('div');
        container.style.display = 'none';
        
        const fields = {
            'year': '<?php echo date("Y"); ?>',
            'date': '<?php echo date("d/m/Y"); ?>',
            'amount': '180,00',
            'description': 'QUOTA ANNUALE ISCRIZIONE',
            'item_description': 'QUOTA ANNUALE ASSOCIAZIONE - IEVA FRANCESCO',
            'billing_details': "<strong>TECCOND SRLS</strong><br>Piazza della Libertà 3<br>per <strong>IEVA FRANCESCO</strong><br>C.F./P.IVA: 15995851001",
            'payment_date': '<?php echo date("d/m/Y"); ?>'
        };
        
        for (const [key, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            container.appendChild(input);
        }
        
        form.appendChild(container);
        
        // Dobbiamo dire al controller di usare l'HTML inviato nel POST, non quello su file, 
        // ALTRIMENTI stiamo vedendo l'anteprima del FILE SALVATO, non delle modifiche correnti.
        // previewManualReceiptPdf legge da FILE.
        // Dobbiamo creare un metodo che accetta HTML custom.
        
        // Modifichiamo il form action verso una nuova rotta che gestisce anteprima PDF da HTML raw
        form.action = "<?php echo \App\Core\Helpers::url('/settings/ricevute/preview-pdf-raw'); ?>";
        form.target = "_blank";
        form.submit();
        
        // Cleanup
        form.removeChild(container);
        form.action = originalAction;
        form.target = "_self";
    }

    document.addEventListener('DOMContentLoaded', function() {
        tinymce.init({
            selector: '#html_content',
            height: 800,
            plugins: 'fullpage code table lists image link hr pagebreak',
            toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | table | code fullpage',
            fullpage_default_doctype: "<!DOCTYPE html>",
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
            }
        });
    });
    </script>
  </div>
</div>