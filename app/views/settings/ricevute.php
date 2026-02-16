<?php
use App\Core\Helpers;
$row = $data['row'] ?? [];
?>

<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="card-title mb-0">Gestione Template Ricevute</h4>
        <a href="<?php echo Helpers::url('/settings'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-arrow-left"></i> Torna a Settings
        </a>
    </div>

    <div class="alert alert-info">
        Carica il template PDF per le ricevute. I campi (Numero, Data, Importo, ecc.) verranno sovraimpressi secondo le coordinate impostate.
    </div>

    <hr class="my-4">
    
    <h5 class="mb-3">Template Corrente</h5>
    <p class="mb-2">File: <strong><?php echo htmlspecialchars($row['receipt_template_path'] ?? 'Nessun template caricato'); ?></strong></p>
    
    <?php if (!empty($row['receipt_template_path'])) { ?>
      <a class="btn btn-sm btn-outline-primary mb-3" href="<?php echo Helpers::url('/documents/download?path=' . urlencode($row['receipt_template_path'])); ?>">
        <i class="ti ti-download"></i> Scarica template attuale
      </a>
    <?php } ?>

    <form method="post" enctype="multipart/form-data" action="<?php echo Helpers::url('/settings/ricevute/update-template'); ?>" class="mb-4">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      <div class="row g-3">
          <div class="col-md-8">
              <label class="form-label">Carica Nuovo Template (PDF)</label>
              <div class="input-group">
                <input type="file" name="template" accept=".pdf" class="form-control" required>
                <button class="btn btn-primary" type="submit">Carica PDF</button>
              </div>
              <div class="form-text">Il file deve essere un PDF standard (A4).</div>
          </div>
          <div class="col-md-4">
              <label class="form-label">Orientamento</label>
              <select name="orientation" class="form-select">
                  <option value="P" <?php echo ($row['receipt_orientation'] ?? 'P') === 'P' ? 'selected' : ''; ?>>Verticale (Portrait)</option>
                  <option value="L" <?php echo ($row['receipt_orientation'] ?? 'P') === 'L' ? 'selected' : ''; ?>>Orizzontale (Landscape)</option>
              </select>
          </div>
      </div>
    </form>

    <hr class="my-4">
    
    <h5 class="mb-3">Configurazione Timbro Digitale (Editor Visuale)</h5>
    <p class="text-muted small mb-3">
        Trascina i campi sul template per posizionarli.
    </p>

    <div id="pdf-editor-container" style="position: relative; border: 1px solid #ccc; background: #f0f0f0; overflow: auto; min-height: 500px; margin-bottom: 20px;">
        <canvas id="pdf-render" style="display: block; margin: 0 auto;"></canvas>
        <canvas id="pdf-grid" style="position: absolute; top: 0; left: 0; pointer-events: none; display: none;"></canvas>
        
        <!-- Draggable Elements -->
        <div id="drag-number" class="drag-item" data-field="number" style="position: absolute; left: 0; top: 0; background: rgba(0, 0, 255, 0.3); border: 1px solid blue; padding: 2px; cursor: move; font-weight: bold; white-space: nowrap;">NUMERO</div>
        <div id="drag-date" class="drag-item" data-field="date" style="position: absolute; left: 0; top: 0; background: rgba(255, 165, 0, 0.3); border: 1px solid orange; padding: 2px; cursor: move; font-weight: bold; white-space: nowrap;">DATA</div>
        <div id="drag-name" class="drag-item" data-field="name" style="position: absolute; left: 0; top: 0; background: rgba(255, 0, 0, 0.3); border: 1px solid red; padding: 2px; cursor: move; font-weight: bold; white-space: nowrap;">NOME</div>
        <div id="drag-address" class="drag-item" data-field="address" style="position: absolute; left: 0; top: 0; background: rgba(0, 128, 0, 0.3); border: 1px solid green; padding: 2px; cursor: move; font-weight: bold; white-space: nowrap;">INDIRIZZO</div>
        <div id="drag-cf" class="drag-item" data-field="cf" style="position: absolute; left: 0; top: 0; background: rgba(128, 0, 128, 0.3); border: 1px solid purple; padding: 2px; cursor: move; font-weight: bold; white-space: nowrap;">C.F./P.IVA</div>
        <div id="drag-amount" class="drag-item" data-field="amount" style="position: absolute; left: 0; top: 0; background: rgba(0, 0, 0, 0.3); border: 1px solid black; padding: 2px; cursor: move; font-weight: bold; white-space: nowrap;">IMPORTO</div>
        <div id="drag-description" class="drag-item" data-field="description" style="position: absolute; left: 0; top: 0; background: rgba(0, 128, 128, 0.3); border: 1px solid teal; padding: 2px; cursor: move; font-weight: bold; white-space: nowrap;">CAUSALE</div>
    </div>

    <form method="post" action="<?php echo Helpers::url('/settings/ricevute/update-stamp'); ?>" id="stampForm">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      
      <div class="row g-3">
          <?php 
          $fields = [
              'number' => 'Numero',
              'date' => 'Data',
              'name' => 'Nome Socio',
              'address' => 'Indirizzo',
              'cf' => 'Codice Fiscale',
              'amount' => 'Importo',
              'description' => 'Causale'
          ];
          
          foreach ($fields as $key => $label) {
              $dbPrefix = 'receipt_stamp_receipt_' . $key; // default
              if ($key === 'name') $dbPrefix = 'receipt_stamp_member_name';
              if ($key === 'address') $dbPrefix = 'receipt_stamp_member_address';
              if ($key === 'cf') $dbPrefix = 'receipt_stamp_member_cf';
              if ($key === 'amount') $dbPrefix = 'receipt_stamp_amount';
              if ($key === 'description') $dbPrefix = 'receipt_stamp_description';
              if ($key === 'number') $dbPrefix = 'receipt_stamp_receipt_number';
              if ($key === 'date') $dbPrefix = 'receipt_stamp_receipt_date';
          ?>
          <div class="col-md-6 col-lg-3">
            <div class="card bg-light border-0">
                <div class="card-body p-3">
                    <h6 class="text-primary mb-2"><?php echo htmlspecialchars($label); ?></h6>
                    <div class="mb-2">
                        <label class="form-label small mb-1">X (mm)</label>
                        <input type="number" name="<?php echo $key; ?>_x" id="in_<?php echo $key; ?>_x" class="form-control form-control-sm" value="<?php echo (int)($row[$dbPrefix.'_x'] ?? 0); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Y (mm)</label>
                        <input type="number" name="<?php echo $key; ?>_y" id="in_<?php echo $key; ?>_y" class="form-control form-control-sm" value="<?php echo (int)($row[$dbPrefix.'_y'] ?? 0); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Size</label>
                        <input type="number" name="<?php echo $key; ?>_font_size" class="form-control form-control-sm" value="<?php echo (int)($row[$dbPrefix.'_font_size'] ?? 12); ?>">
                    </div>
                    <!-- Color, Font, Bold omessi per brevità ma aggiungibili se serve -->
                </div>
            </div>
          </div>
          <?php } ?>
      </div>

      <div class="d-flex gap-2 mt-4 align-items-center">
        <button type="submit" class="btn btn-primary">Salva Configurazione</button>
        <button type="button" class="btn btn-outline-dark" onclick="previewStamp()">Anteprima</button>
        <div class="form-check ms-3">
            <input class="form-check-input" type="checkbox" name="debug_grid" id="debug_grid" value="1" onchange="toggleGrid(this.checked)">
            <label class="form-check-label small" for="debug_grid">Mostra Griglia Calibrazione</label>
        </div>
      </div>
    </form>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/interact.js/1.10.17/interact.min.js"></script>

    <script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

    const pdfUrl = "<?php echo \App\Core\Helpers::url('/documents/download?path=' . urlencode($row['receipt_template_path'] ?? '')); ?>";
    const isPdf = "<?php echo strtolower(pathinfo($row['receipt_template_path'] ?? '', PATHINFO_EXTENSION)) === 'pdf' ? '1' : '0'; ?>";

    let pdfDoc = null;
    let pageNum = 1;
    let scale = 1.0;
    let canvas = document.getElementById('pdf-render');
    let gridCanvas = document.getElementById('pdf-grid');
    let ctx = canvas.getContext('2d');
    let gridCtx = gridCanvas.getContext('2d');
    let pdfViewport = null;
    let fpdiWidthMm = 0;

    if (isPdf === '1' && pdfUrl) {
        const loadingTask = pdfjsLib.getDocument({
             url: pdfUrl,
             withCredentials: true,
             cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/cmaps/',
             cMapPacked: true
        });
        
        loadingTask.promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            renderPage(pageNum);
        }).catch(err => {
            console.error('PDF.js Error:', err);
            document.getElementById('pdf-editor-container').innerHTML = '<p class="text-center text-danger p-5">Errore caricamento PDF: ' + err.message + '</p>';
        });
    } else {
        document.getElementById('pdf-editor-container').innerHTML = '<p class="text-center p-5">Carica un template PDF per usare l\'editor visuale.</p>';
    }

    function renderPage(num) {
        pdfDoc.getPage(num).then(function(page) {
            const containerWidth = document.getElementById('pdf-editor-container').clientWidth - 40;
            const unscaledViewport = page.getViewport({scale: 1});
            
            fpdiWidthMm = (unscaledViewport.width / 72) * 25.4;
            
            scale = containerWidth / unscaledViewport.width;
            if(scale > 1.5) scale = 1.5;
            
            pdfViewport = page.getViewport({scale: scale});

            canvas.height = pdfViewport.height;
            canvas.width = pdfViewport.width;
            
            gridCanvas.height = pdfViewport.height;
            gridCanvas.width = pdfViewport.width;
            gridCanvas.style.left = canvas.offsetLeft + 'px';

            const renderContext = {
                canvasContext: ctx,
                viewport: pdfViewport
            };
            page.render(renderContext).promise.then(() => {
                initDraggables();
                drawGrid();
            });
        });
    }

    function drawGrid() {
        if (!fpdiWidthMm) return;
        gridCtx.clearRect(0, 0, gridCanvas.width, gridCanvas.height);
        const pxPerMm = gridCanvas.width / fpdiWidthMm;
        
        gridCtx.strokeStyle = 'rgba(0, 255, 255, 0.5)';
        gridCtx.lineWidth = 1;
        gridCtx.font = '10px Arial';
        gridCtx.fillStyle = 'rgba(0, 0, 255, 0.8)';
        
        for (let mm = 0; mm <= fpdiWidthMm; mm += 10) {
            const x = mm * pxPerMm;
            gridCtx.beginPath(); gridCtx.moveTo(x, 0); gridCtx.lineTo(x, gridCanvas.height); gridCtx.stroke();
            gridCtx.fillText(mm, x + 2, 10);
        }
        const heightMm = gridCanvas.height / pxPerMm;
        for (let mm = 0; mm <= heightMm; mm += 10) {
            const y = mm * pxPerMm;
            gridCtx.beginPath(); gridCtx.moveTo(0, y); gridCtx.lineTo(gridCanvas.width, y); gridCtx.stroke();
            gridCtx.fillText(mm, 2, y - 2);
        }
    }

    function initDraggables() {
        if (!fpdiWidthMm) return;

        const fields = ['number', 'date', 'name', 'address', 'cf', 'amount', 'description'];
        const pxPerMm = canvas.width / fpdiWidthMm;
        const offsetX = canvas.offsetLeft;
        const offsetY = canvas.offsetTop;

        const mmToPx = (mm) => mm * pxPerMm;
        const pxToMm = (px) => px / pxPerMm;
        
        fields.forEach(field => {
            const el = document.getElementById('drag-' + field);
            const inputX = document.getElementById('in_' + field + '_x');
            const inputY = document.getElementById('in_' + field + '_y');
            
            el.style.transformOrigin = "center center";
            const rect = el.getBoundingClientRect();
            const elW = rect.width;
            const elH = rect.height;

            let startX = (mmToPx(parseFloat(inputX.value) || 0) + offsetX) - (elW / 2);
            let startY = (mmToPx(parseFloat(inputY.value) || 0) + offsetY) - (elH / 2);
            
            el.style.transform = `translate(${startX}px, ${startY}px)`;
            el.setAttribute('data-x', startX);
            el.setAttribute('data-y', startY);

            interact(el).draggable({
                listeners: {
                    move (event) {
                        const target = event.target;
                        const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                        const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

                        target.style.transform = `translate(${x}px, ${y}px)`;
                        target.setAttribute('data-x', x);
                        target.setAttribute('data-y', y);
                        
                        const cx = x + (target.offsetWidth / 2);
                        const cy = y + (target.offsetHeight / 2);

                        const mmX = Math.round(pxToMm(cx - offsetX));
                        const mmY = Math.round(pxToMm(cy - offsetY));
                        
                        document.getElementById('in_' + target.dataset.field + '_x').value = mmX;
                        document.getElementById('in_' + target.dataset.field + '_y').value = mmY;
                    }
                }
            });
            
            const updateFromInput = () => {
                const cx = mmToPx(parseFloat(inputX.value) || 0) + offsetX;
                const cy = mmToPx(parseFloat(inputY.value) || 0) + offsetY;
                const x = cx - (el.offsetWidth / 2);
                const y = cy - (el.offsetHeight / 2);
                el.style.transform = `translate(${x}px, ${y}px)`;
                el.setAttribute('data-x', x);
                el.setAttribute('data-y', y);
            };
            inputX.addEventListener('input', updateFromInput);
            inputY.addEventListener('input', updateFromInput);
        });
    }

    function previewStamp() {
        const form = document.getElementById('stampForm');
        const originalAction = form.action;
        form.action = "<?php echo \App\Core\Helpers::url('/settings/ricevute/preview-stamp'); ?>";
        form.target = "_blank";
        form.submit();
        form.action = originalAction;
        form.target = "_self";
    }

    function toggleGrid(show) {
        const grid = document.getElementById('pdf-grid');
        grid.style.display = show ? 'block' : 'none';
        if(show) drawGrid();
    }
    </script>
  </div>
</div>
