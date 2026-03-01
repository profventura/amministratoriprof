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
      </div>
    </form>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.7/tinymce.min.js" referrerpolicy="no-referrer"></script>
    <script>
    function previewHtml() {
        const form = document.getElementById('htmlForm');
        const originalAction = form.action;
        form.action = "<?php echo \App\Core\Helpers::url('/settings/ricevute/preview-html'); ?>";
        form.target = "_blank";
        form.submit();
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