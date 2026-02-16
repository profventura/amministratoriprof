<?php
use App\Core\Helpers;
$row = $data['row'] ?? [];
?>

<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="card-title mb-0">Gestione Template Certificati (Iscrizione)</h4>
        <a href="<?php echo Helpers::url('/settings'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-arrow-left"></i> Torna a Settings
        </a>
    </div>

    <div class="alert alert-info">
        In questa sezione puoi caricare il template PDF per i certificati di iscrizione annuale e configurare la posizione dei testi (timbro digitale).
    </div>

    <hr class="my-4">
    
    <h5 class="mb-3">Template Corrente</h5>
    <p class="mb-2">File: <strong><?php echo htmlspecialchars($row['membership_certificate_template_docx_path'] ?? 'Nessun template caricato'); ?></strong></p>
    
    <?php if (!empty($row['membership_certificate_template_docx_path'])) { ?>
      <a class="btn btn-sm btn-outline-primary mb-3" href="<?php echo Helpers::url('/documents/download?path=' . urlencode($row['membership_certificate_template_docx_path'])); ?>">
        <i class="ti ti-download"></i> Scarica template attuale
      </a>
    <?php } ?>

    <form method="post" enctype="multipart/form-data" action="<?php echo Helpers::url('/settings/update-template'); ?>" class="mb-4">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      <div class="row g-3">
          <div class="col-md-8">
              <label class="form-label">Carica Nuovo Template (PDF)</label>
              <div class="input-group">
                <input type="file" name="template" accept=".pdf" class="form-control" required>
                <button class="btn btn-primary" type="submit">Carica PDF</button>
              </div>
              <div class="form-text">Il file deve essere un PDF standard.</div>
          </div>
          <div class="col-md-4">
              <label class="form-label">Orientamento</label>
              <select name="orientation" class="form-select">
                  <option value="P" <?php echo ($row['certificate_orientation'] ?? 'P') === 'P' ? 'selected' : ''; ?>>Verticale (Portrait)</option>
                  <option value="L" <?php echo ($row['certificate_orientation'] ?? 'P') === 'L' ? 'selected' : ''; ?>>Orizzontale (Landscape)</option>
              </select>
          </div>
      </div>
    </form>

    <hr class="my-4">
    
    <h5 class="mb-3">Configurazione Timbro Digitale (Editor Visuale)</h5>
    <p class="text-muted small mb-3">
        Carica un template PDF sopra, poi usa questo editor per posizionare i campi. 
        Trascina i box colorati dove vuoi che appaiano i testi. Quando hai finito, clicca "Salva Configurazione".
    </p>

    <div id="pdf-editor-container" style="position: relative; border: 1px solid #ccc; background: #f0f0f0; overflow: auto; min-height: 500px; margin-bottom: 20px;">
        <canvas id="pdf-render" style="display: block; margin: 0 auto;"></canvas>
        <canvas id="pdf-grid" style="position: absolute; top: 0; left: 0; pointer-events: none; display: none;"></canvas>
        
        <!-- Draggable Elements -->
        <div id="drag-name" class="drag-item" data-field="name" style="position: absolute; left: 0; top: 0; background: rgba(255, 0, 0, 0.3); border: 1px solid red; padding: 2px; cursor: move; font-weight: bold; white-space: nowrap;">NOME</div>
        <div id="drag-number" class="drag-item" data-field="number" style="position: absolute; left: 0; top: 0; background: rgba(0, 0, 255, 0.3); border: 1px solid blue; padding: 2px; cursor: move; font-weight: bold; white-space: nowrap;">NUMERO</div>
        <div id="drag-year" class="drag-item" data-field="year" style="position: absolute; left: 0; top: 0; background: rgba(0, 128, 0, 0.3); border: 1px solid green; padding: 2px; cursor: move; font-weight: bold; white-space: nowrap;">ANNO</div>
        <div id="drag-date" class="drag-item" data-field="date" style="position: absolute; left: 0; top: 0; background: rgba(255, 165, 0, 0.3); border: 1px solid orange; padding: 2px; cursor: move; font-weight: bold; white-space: nowrap;">DATA</div>
    </div>

    <form method="post" action="<?php echo Helpers::url('/settings/update-stamp'); ?>" id="stampForm">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      
      <div class="row g-3">
          <!-- NOME -->
          <div class="col-md-6 col-lg-3">
            <div class="card bg-light border-0">
                <div class="card-body p-3">
                    <h6 class="text-primary mb-2">Nome Socio</h6>
                    <div class="mb-2">
                        <label class="form-label small mb-1">X (mm)</label>
                        <input type="number" name="name_x" id="in_name_x" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_name_x'] ?? 100); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Y (mm)</label>
                        <input type="number" name="name_y" id="in_name_y" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_name_y'] ?? 120); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Dimensione Font</label>
                        <input type="number" name="name_font_size" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_name_font_size'] ?? 16); ?>">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="name_bold" value="1" <?php echo ($row['certificate_stamp_name_bold']??1)?'checked':''; ?>>
                        <label class="form-check-label small">Grassetto</label>
                    </div>
                </div>
            </div>
          </div>

          <!-- NUMERO -->
          <div class="col-md-6 col-lg-3">
            <div class="card bg-light border-0">
                <div class="card-body p-3">
                    <h6 class="text-primary mb-2">Numero Socio</h6>
                    <div class="mb-2">
                        <label class="form-label small mb-1">X (mm)</label>
                        <input type="number" name="number_x" id="in_number_x" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_number_x'] ?? 100); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Y (mm)</label>
                        <input type="number" name="number_y" id="in_number_y" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_number_y'] ?? 140); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Dimensione Font</label>
                        <input type="number" name="number_font_size" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_number_font_size'] ?? 16); ?>">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="number_bold" value="1" <?php echo ($row['certificate_stamp_number_bold']??1)?'checked':''; ?>>
                        <label class="form-check-label small">Grassetto</label>
                    </div>
                </div>
            </div>
          </div>

          <!-- ANNO -->
          <div class="col-md-6 col-lg-3">
            <div class="card bg-light border-0">
                <div class="card-body p-3">
                    <h6 class="text-primary mb-2">Anno</h6>
                    <div class="mb-2">
                        <label class="form-label small mb-1">X (mm)</label>
                        <input type="number" name="year_x" id="in_year_x" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_year_x'] ?? 0); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Y (mm)</label>
                        <input type="number" name="year_y" id="in_year_y" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_year_y'] ?? 0); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Dimensione Font</label>
                        <input type="number" name="year_font_size" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_year_font_size'] ?? 12); ?>">
                    </div>
                </div>
            </div>
          </div>

          <!-- DATA -->
          <div class="col-md-6 col-lg-3">
            <div class="card bg-light border-0">
                <div class="card-body p-3">
                    <h6 class="text-primary mb-2">Data Emissione</h6>
                    <div class="mb-2">
                        <label class="form-label small mb-1">X (mm)</label>
                        <input type="number" name="date_x" id="in_date_x" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_date_x'] ?? 0); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Y (mm)</label>
                        <input type="number" name="date_y" id="in_date_y" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_date_y'] ?? 0); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Dimensione Font</label>
                        <input type="number" name="date_font_size" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_date_font_size'] ?? 12); ?>">
                    </div>
                </div>
            </div>
          </div>
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

    <!-- Librerie PDF.js e Interact.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/interact.js/1.10.17/interact.min.js"></script>

    <script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

    const pdfUrl = "<?php echo \App\Core\Helpers::url('/documents/download?path=' . urlencode($row['membership_certificate_template_docx_path'] ?? '')); ?>";
    const isPdf = "<?php echo strtolower(pathinfo($row['membership_certificate_template_docx_path'] ?? '', PATHINFO_EXTENSION)) === 'pdf' ? '1' : '0'; ?>";

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
        fetch("<?php echo \App\Core\Helpers::url('/settings/pdf-geometry'); ?>")
            .then(r => r.json())
            .then(geom => {
                if (geom.error) { 
                    console.error(geom.error); 
                    fpdiWidthMm = 210; 
                } else {
                    fpdiWidthMm = parseFloat(geom.width);
                }
                
                // Importante: Passare withCredentials se necessario, ma qui l'auth è via cookie di sessione standard
                // PDF.js potrebbe aver bisogno di opzioni per la richiesta
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
                    document.getElementById('pdf-editor-container').innerHTML = '<p class="text-center text-danger p-5">Errore caricamento PDF: ' + err.message + '<br><small>Verifica che il file sia un PDF valido.</small></p>';
                });
            })
            .catch(err => {
                console.error('Fetch geometry error:', err);
                document.getElementById('pdf-editor-container').innerHTML = '<p class="text-center text-danger p-5">Errore comunicazione server: ' + err.message + '</p>';
            });
    } else {
        document.getElementById('pdf-editor-container').innerHTML = '<p class="text-center p-5">Carica un template PDF per usare l\'editor visuale.</p>';
    }

    function renderPage(num) {
        pdfDoc.getPage(num).then(function(page) {
            const containerWidth = document.getElementById('pdf-editor-container').clientWidth - 40;
            const unscaledViewport = page.getViewport({scale: 1});
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

        const fields = ['name', 'number', 'year', 'date'];
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
        form.action = "<?php echo \App\Core\Helpers::url('/settings/preview-stamp'); ?>";
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
