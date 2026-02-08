<div class="card">
  <div class="card-body">
    <h4 class="mb-3">Template certificato DM (DOCX o PDF)</h4>
    <p class="mb-2">Percorso corrente: <strong><?php echo htmlspecialchars($row['dm_certificate_template_docx_path'] ?? ''); ?></strong></p>
    <?php if (!empty($row['dm_certificate_template_docx_path'])) { ?>
      <a class="btn btn-sm btn-outline-primary mb-3" href="<?php echo \App\Core\Helpers::url('/documents/download?path=' . urlencode($row['dm_certificate_template_docx_path'])); ?>">Scarica template</a>
    <?php } ?>
    <p class="card-text">Carica un file <strong>.docx</strong> (per sostituzione placeholder) oppure <strong>.pdf</strong> (per sovrapposizione testo con coordinate) da usare come template per i certificati.</p>
    <form method="post" enctype="multipart/form-data" action="<?php echo \App\Core\Helpers::url('/settings/update-template'); ?>">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      <div class="mb-3">
        <input type="file" name="template" accept=".docx,.pdf" class="form-control" required>
      </div>
      <p class="text-muted mb-2">Placeholder supportati nel DOCX:</p>
      <ul class="mb-3">
        <li>${nome} → nome e cognome</li>
        <li>${te} → numero socio (id iscrizione per anno)</li>
        <li>${a} → anno del corso</li>
        <li>${data} → data odierna (gg/mm/aaaa)</li>
      </ul>
      <button class="btn btn-primary">Aggiorna Template</button>
    </form>
    <hr class="my-4">
    <h4 class="mb-3">Coordinate sovrascrittura su PDF (Editor Visuale)</h4>
    <p class="text-muted small">Carica un template PDF sopra, poi usa questo editor per posizionare i campi. Trascina i box rossi dove vuoi che appaiano i testi. Quando hai finito, clicca "Salva Coordinate".</p>
    
    <div id="pdf-editor-container" style="position: relative; border: 1px solid #ccc; background: #f0f0f0; overflow: auto; min-height: 500px; margin-bottom: 20px;">
        <canvas id="pdf-render" style="display: block; margin: 0 auto;"></canvas>
        <canvas id="pdf-grid" style="position: absolute; top: 0; left: 0; pointer-events: none; display: none;"></canvas>
        
        <!-- Draggable Elements -->
        <div id="drag-name" class="drag-item" data-field="name" style="position: absolute; left: 0; top: 0; background: rgba(255, 0, 0, 0.3); border: 1px solid red; padding: 2px; cursor: move; font-weight: bold; white-space: nowrap;">NOME</div>
        <div id="drag-number" class="drag-item" data-field="number" style="position: absolute; left: 0; top: 0; background: rgba(0, 0, 255, 0.3); border: 1px solid blue; padding: 2px; cursor: move; font-weight: bold; white-space: nowrap;">NUMERO</div>
        <div id="drag-year" class="drag-item" data-field="year" style="position: absolute; left: 0; top: 0; background: rgba(0, 128, 0, 0.3); border: 1px solid green; padding: 2px; cursor: move; font-weight: bold; white-space: nowrap;">ANNO</div>
        <div id="drag-date" class="drag-item" data-field="date" style="position: absolute; left: 0; top: 0; background: rgba(255, 165, 0, 0.3); border: 1px solid orange; padding: 2px; cursor: move; font-weight: bold; white-space: nowrap;">DATA</div>
    </div>

    <!-- Hidden form populated by JS -->
    <form method="post" action="<?php echo \App\Core\Helpers::url('/settings/update-stamp'); ?>" id="stampForm">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      
      <!-- NOME -->
      <div class="row g-2 mb-3 align-items-end border-bottom pb-2">
        <div class="col-12"><h6 class="mb-0 text-primary">Campo: NOME</h6></div>
        <div class="col-sm-2"><label class="form-label small">X (mm)</label><input type="number" name="name_x" id="in_name_x" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_name_x'] ?? 100); ?>"></div>
        <div class="col-sm-2"><label class="form-label small">Y (mm)</label><input type="number" name="name_y" id="in_name_y" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_name_y'] ?? 120); ?>"></div>
        <div class="col-sm-2"><label class="form-label small">Size</label><input type="number" name="name_font_size" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_name_font_size'] ?? 16); ?>"></div>
        <div class="col-sm-2"><label class="form-label small">Color</label><input type="color" name="name_color" class="form-control form-control-sm form-control-color w-100" value="<?php echo htmlspecialchars($row['certificate_stamp_name_color'] ?? '#000000'); ?>"></div>
        <div class="col-sm-3"><label class="form-label small">Font</label>
            <select name="name_font_family" class="form-select form-select-sm">
                <option value="Arial" <?php echo ($row['certificate_stamp_name_font_family']??'')=='Arial'?'selected':''; ?>>Arial</option>
                <option value="Helvetica" <?php echo ($row['certificate_stamp_name_font_family']??'')=='Helvetica'?'selected':''; ?>>Helvetica</option>
                <option value="Times" <?php echo ($row['certificate_stamp_name_font_family']??'')=='Times'?'selected':''; ?>>Times</option>
                <option value="Courier" <?php echo ($row['certificate_stamp_name_font_family']??'')=='Courier'?'selected':''; ?>>Courier</option>
                <option value="Gill Sans MT" <?php echo ($row['certificate_stamp_name_font_family']??'')=='Gill Sans MT'?'selected':''; ?>>Gill Sans MT</option>
            </select>
        </div>
        <div class="col-sm-1">
            <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" name="name_bold" id="name_bold" value="1" <?php echo ($row['certificate_stamp_name_bold']??1)?'checked':''; ?>>
                <label class="form-check-label small" for="name_bold"><strong>B</strong></label>
            </div>
        </div>
      </div>

      <!-- NUMERO -->
      <div class="row g-2 mb-3 align-items-end border-bottom pb-2">
        <div class="col-12"><h6 class="mb-0 text-primary">Campo: NUMERO</h6></div>
        <div class="col-sm-2"><label class="form-label small">X (mm)</label><input type="number" name="number_x" id="in_number_x" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_number_x'] ?? 100); ?>"></div>
        <div class="col-sm-2"><label class="form-label small">Y (mm)</label><input type="number" name="number_y" id="in_number_y" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_number_y'] ?? 140); ?>"></div>
        <div class="col-sm-2"><label class="form-label small">Size</label><input type="number" name="number_font_size" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_number_font_size'] ?? 16); ?>"></div>
        <div class="col-sm-2"><label class="form-label small">Color</label><input type="color" name="number_color" class="form-control form-control-sm form-control-color w-100" value="<?php echo htmlspecialchars($row['certificate_stamp_number_color'] ?? '#000000'); ?>"></div>
        <div class="col-sm-3"><label class="form-label small">Font</label>
            <select name="number_font_family" class="form-select form-select-sm">
                <option value="Arial" <?php echo ($row['certificate_stamp_number_font_family']??'')=='Arial'?'selected':''; ?>>Arial</option>
                <option value="Helvetica" <?php echo ($row['certificate_stamp_number_font_family']??'')=='Helvetica'?'selected':''; ?>>Helvetica</option>
                <option value="Times" <?php echo ($row['certificate_stamp_number_font_family']??'')=='Times'?'selected':''; ?>>Times</option>
                <option value="Courier" <?php echo ($row['certificate_stamp_number_font_family']??'')=='Courier'?'selected':''; ?>>Courier</option>
                <option value="Gill Sans MT" <?php echo ($row['certificate_stamp_number_font_family']??'')=='Gill Sans MT'?'selected':''; ?>>Gill Sans MT</option>
            </select>
        </div>
        <div class="col-sm-1">
            <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" name="number_bold" id="number_bold" value="1" <?php echo ($row['certificate_stamp_number_bold']??1)?'checked':''; ?>>
                <label class="form-check-label small" for="number_bold"><strong>B</strong></label>
            </div>
        </div>
      </div>

      <!-- ANNO -->
      <div class="row g-2 mb-3 align-items-end border-bottom pb-2">
        <div class="col-12"><h6 class="mb-0 text-primary">Campo: ANNO</h6></div>
        <div class="col-sm-2"><label class="form-label small">X (mm)</label><input type="number" name="year_x" id="in_year_x" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_year_x'] ?? 0); ?>"></div>
        <div class="col-sm-2"><label class="form-label small">Y (mm)</label><input type="number" name="year_y" id="in_year_y" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_year_y'] ?? 0); ?>"></div>
        <div class="col-sm-2"><label class="form-label small">Size</label><input type="number" name="year_font_size" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_year_font_size'] ?? 12); ?>"></div>
        <div class="col-sm-2"><label class="form-label small">Color</label><input type="color" name="year_color" class="form-control form-control-sm form-control-color w-100" value="<?php echo htmlspecialchars($row['certificate_stamp_year_color'] ?? '#000000'); ?>"></div>
        <div class="col-sm-3"><label class="form-label small">Font</label>
            <select name="year_font_family" class="form-select form-select-sm">
                <option value="Arial" <?php echo ($row['certificate_stamp_year_font_family']??'')=='Arial'?'selected':''; ?>>Arial</option>
                <option value="Helvetica" <?php echo ($row['certificate_stamp_year_font_family']??'')=='Helvetica'?'selected':''; ?>>Helvetica</option>
                <option value="Times" <?php echo ($row['certificate_stamp_year_font_family']??'')=='Times'?'selected':''; ?>>Times</option>
                <option value="Courier" <?php echo ($row['certificate_stamp_year_font_family']??'')=='Courier'?'selected':''; ?>>Courier</option>
                <option value="Gill Sans MT" <?php echo ($row['certificate_stamp_year_font_family']??'')=='Gill Sans MT'?'selected':''; ?>>Gill Sans MT</option>
            </select>
        </div>
        <div class="col-sm-1">
            <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" name="year_bold" id="year_bold" value="1" <?php echo ($row['certificate_stamp_year_bold']??0)?'checked':''; ?>>
                <label class="form-check-label small" for="year_bold"><strong>B</strong></label>
            </div>
        </div>
      </div>

      <!-- DATA -->
      <div class="row g-2 mb-3 align-items-end border-bottom pb-2">
        <div class="col-12"><h6 class="mb-0 text-primary">Campo: DATA</h6></div>
        <div class="col-sm-2"><label class="form-label small">X (mm)</label><input type="number" name="date_x" id="in_date_x" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_date_x'] ?? 0); ?>"></div>
        <div class="col-sm-2"><label class="form-label small">Y (mm)</label><input type="number" name="date_y" id="in_date_y" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_date_y'] ?? 0); ?>"></div>
        <div class="col-sm-2"><label class="form-label small">Size</label><input type="number" name="date_font_size" class="form-control form-control-sm" value="<?php echo (int)($row['certificate_stamp_date_font_size'] ?? 12); ?>"></div>
        <div class="col-sm-2"><label class="form-label small">Color</label><input type="color" name="date_color" class="form-control form-control-sm form-control-color w-100" value="<?php echo htmlspecialchars($row['certificate_stamp_date_color'] ?? '#000000'); ?>"></div>
        <div class="col-sm-3"><label class="form-label small">Font</label>
            <select name="date_font_family" class="form-select form-select-sm">
                <option value="Arial" <?php echo ($row['certificate_stamp_date_font_family']??'')=='Arial'?'selected':''; ?>>Arial</option>
                <option value="Helvetica" <?php echo ($row['certificate_stamp_date_font_family']??'')=='Helvetica'?'selected':''; ?>>Helvetica</option>
                <option value="Times" <?php echo ($row['certificate_stamp_date_font_family']??'')=='Times'?'selected':''; ?>>Times</option>
                <option value="Courier" <?php echo ($row['certificate_stamp_date_font_family']??'')=='Courier'?'selected':''; ?>>Courier</option>
                <option value="Gill Sans MT" <?php echo ($row['certificate_stamp_date_font_family']??'')=='Gill Sans MT'?'selected':''; ?>>Gill Sans MT</option>
            </select>
        </div>
        <div class="col-sm-1">
            <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" name="date_bold" id="date_bold" value="1" <?php echo ($row['certificate_stamp_date_bold']??0)?'checked':''; ?>>
                <label class="form-check-label small" for="date_bold"><strong>B</strong></label>
            </div>
        </div>
      </div>

      <div class="d-flex gap-2 mt-3 align-items-center">
        <button type="submit" class="btn btn-primary">Salva Coordinate</button>
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

    const pdfUrl = "<?php echo \App\Core\Helpers::url('/documents/download?path=' . urlencode($row['dm_certificate_template_docx_path'] ?? '')); ?>";
    const isPdf = "<?php echo strtolower(pathinfo($row['dm_certificate_template_docx_path'] ?? '', PATHINFO_EXTENSION)) === 'pdf' ? '1' : '0'; ?>";

    let pdfDoc = null;
    let pageNum = 1;
    let scale = 1.0; // Scala visuale, ricalcolata in base alla larghezza contenitore
    let canvas = document.getElementById('pdf-render');
    let gridCanvas = document.getElementById('pdf-grid');
    let ctx = canvas.getContext('2d');
    let gridCtx = gridCanvas.getContext('2d');
    let pdfViewport = null;

    let fpdiWidthMm = 0; // Larghezza reale vista da FPDI

    if (isPdf === '1' && pdfUrl) {
        // 1. Ottieni geometria reale da FPDI
        fetch("<?php echo \App\Core\Helpers::url('/settings/pdf-geometry'); ?>")
            .then(r => r.json())
            .then(geom => {
                if (geom.error) { 
                    console.error(geom.error); 
                    // Fallback: assume A4 landscape if geometry fails? Or just A4 portrait?
                    // Let's default to standard A4 (210mm width) if error to avoid breaking UI completely
                    fpdiWidthMm = 210; 
                } else {
                    fpdiWidthMm = parseFloat(geom.width); // FPDI usa mm di default
                }
                
                // 2. Renderizza PDF.js
                pdfjsLib.getDocument(pdfUrl).promise.then(function(pdfDoc_) {
                    pdfDoc = pdfDoc_;
                    renderPage(pageNum);
                }).catch(err => {
                    console.error('PDF.js Error:', err);
                    document.getElementById('pdf-editor-container').innerHTML = '<p class="text-center text-danger p-5">Errore caricamento PDF: ' + err.message + '</p>';
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
            
            // Align grid canvas
            gridCanvas.height = pdfViewport.height;
            gridCanvas.width = pdfViewport.width;
            gridCanvas.style.left = canvas.offsetLeft + 'px'; // Center align if canvas is centered

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
        
        // Clear grid
        gridCtx.clearRect(0, 0, gridCanvas.width, gridCanvas.height);
        
        const pxPerMm = gridCanvas.width / fpdiWidthMm;
        
        gridCtx.strokeStyle = 'rgba(0, 255, 255, 0.5)';
        gridCtx.lineWidth = 1;
        gridCtx.font = '10px Arial';
        gridCtx.fillStyle = 'rgba(0, 0, 255, 0.8)';
        
        // Vertical lines (X)
        for (let mm = 0; mm <= fpdiWidthMm; mm += 10) {
            const x = mm * pxPerMm;
            gridCtx.beginPath();
            gridCtx.moveTo(x, 0);
            gridCtx.lineTo(x, gridCanvas.height);
            gridCtx.stroke();
            gridCtx.fillText(mm, x + 2, 10);
        }
        
        // Horizontal lines (Y)
        const heightMm = gridCanvas.height / pxPerMm;
        for (let mm = 0; mm <= heightMm; mm += 10) {
            const y = mm * pxPerMm;
            gridCtx.beginPath();
            gridCtx.moveTo(0, y);
            gridCtx.lineTo(gridCanvas.width, y);
            gridCtx.stroke();
            gridCtx.fillText(mm, 2, y - 2);
        }
    }

    function initDraggables() {
        if (!fpdiWidthMm) return; // Aspetta geometria

        const fields = ['name', 'number', 'year', 'date'];
        
        // Calcolo preciso: mappa pixel canvas -> mm FPDI
        // canvas.width (px) corrisponde a fpdiWidthMm (mm)
        const pxPerMm = canvas.width / fpdiWidthMm;
        
        // Offset del canvas rispetto al container (per gestire margin: 0 auto)
        const offsetX = canvas.offsetLeft;
        const offsetY = canvas.offsetTop;

        const mmToPx = (mm) => mm * pxPerMm;
        const pxToMm = (px) => px / pxPerMm;
        
        fields.forEach(field => {
            const el = document.getElementById('drag-' + field);
            const inputX = document.getElementById('in_' + field + '_x');
            const inputY = document.getElementById('in_' + field + '_y');
            
            // Centratura visiva: trasla l'elemento del 50% rispetto al suo stesso centro
            // In CSS, transform: translate(-50%, -50%) sposta l'origine al centro dell'elemento.
            // Ma qui stiamo usando translate(Xpx, Ypx) assoluto.
            // Per far sì che (X,Y) sia il centro dell'elemento, dobbiamo sottrarre metà width/height.
            // Siccome width/height variano (testo), e interact.js gestisce posizioni top-left...
            
            // Modifica: Cambiamo lo stile degli elementi per renderli centrati sul punto
            el.style.transformOrigin = "center center";
            // Ma interact.js lavora su coordinate translate top-left.
            
            // Soluzione: Quando calcoliamo startX/Y (pixel container), sottraiamo metà della larghezza dell'elemento
            // per centrarlo visivamente sul cursore/punto.
            const rect = el.getBoundingClientRect();
            const elW = rect.width;
            const elH = rect.height;

            // Posizionamento iniziale: Coordinate DB (mm) -> Pixel Container
            // X db è il centro. Quindi startX (left) = X_px - (elW / 2)
            let startX = (mmToPx(parseFloat(inputX.value) || 0) + offsetX) - (elW / 2);
            let startY = (mmToPx(parseFloat(inputY.value) || 0) + offsetY) - (elH / 2);
            
            el.style.transform = `translate(${startX}px, ${startY}px)`;
            el.setAttribute('data-x', startX);
            el.setAttribute('data-y', startY);

            interact(el).draggable({
                listeners: {
                    move (event) {
                        const target = event.target;
                        const rect = target.getBoundingClientRect(); // Ricalcola se cambia dimensione? No, fisso per ora.
                        // interact.js usa dx/dy relativi
                        const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                        const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

                        target.style.transform = `translate(${x}px, ${y}px)`;
                        target.setAttribute('data-x', x);
                        target.setAttribute('data-y', y);
                        
                        // Pixel -> Coordinate DB
                        // X_db (centro) = (Left_px + width/2 - offset)
                        // Ma dobbiamo usare la larghezza dell'elemento *non scalato*? 
                        // rect.width è la larghezza renderizzata.
                        
                        // Semplificazione: usiamo target.offsetWidth
                        const cx = x + (target.offsetWidth / 2);
                        const cy = y + (target.offsetHeight / 2);

                        const mmX = Math.round(pxToMm(cx - offsetX));
                        const mmY = Math.round(pxToMm(cy - offsetY));
                        
                        document.getElementById('in_' + target.dataset.field + '_x').value = mmX;
                        document.getElementById('in_' + target.dataset.field + '_y').value = mmY;
                    }
                }
            });
            
            // Listener input
            const updateFromInput = () => {
                // Input (Centro mm) -> Left px
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


    <hr class="my-4">
    <form method="post" action="<?php echo \App\Core\Helpers::url('/settings/test-docx'); ?>">
      <input type="hidden" name="csrf" value="<?php echo \App\Core\CSRF::token(); ?>">
      <button class="btn btn-secondary">Test generazione da DOCX</button>
    </form>
    <p class="mt-2 text-muted">Il test richiede ZipArchive, Dompdf e GD per il PDF; in assenza, genera solo il DOCX di prova.</p>
  </div>
 </div>
