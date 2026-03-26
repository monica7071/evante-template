@extends('layouts.app')

@section('title', 'Manage Mappings - ' . ucfirst($template->contract_type))

@section('styles')
<style>
    /* Page header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .page-header-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    .mode-toggle .btn {
        min-width: 100px;
        border-radius: var(--radius-sm);
        font-weight: 600;
        font-size: 0.82rem;
    }

    /* Layout */
    .mapping-layout {
        display: flex;
        gap: 1.25rem;
        flex-wrap: wrap;
    }
    .left-panel { flex: 1 1 60%; min-width: 0; }
    .right-panel { flex: 0 0 380px; }
    @media (max-width: 1199px) {
        .left-panel, .right-panel { flex: 1 1 100%; }
        .right-panel { flex-basis: 100%; }
    }

    /* Section card */
    .section-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .section-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.85rem 1.15rem;
        border-bottom: 1px solid var(--border);
        background: var(--bg);
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--text-dark);
    }
    .section-card-header i {
        color: var(--primary);
        margin-right: 0.5rem;
    }
    .section-card-header .badge {
        font-size: 0.7rem;
        font-weight: 600;
        background: var(--primary-muted);
        color: var(--primary);
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
    }
    .section-card-body {
        padding: 1rem 1.15rem;
    }

    /* PDF Canvas */
    #pdf-wrapper {
        position: relative;
        display: inline-block;
        cursor: crosshair;
    }
    #pdf-canvas {
        display: block;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        max-width: none !important;
    }
    #pdf-container {
        overflow: auto;
        text-align: left;
        padding: 0.5rem;
    }
    #marker-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }

    /* Page nav */
    .page-nav {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .page-nav .btn {
        border-radius: var(--radius-sm);
        font-size: 0.8rem;
        font-weight: 600;
    }
    .page-nav span {
        font-size: 0.82rem;
        font-weight: 500;
        color: var(--text-mid);
    }

    /* Mapping markers on PDF */
    .mapping-marker {
        position: absolute;
        font-size: var(--marker-font-size, 10px);
        font-family: 'Inter', sans-serif;
        border-radius: 2px;
        cursor: grab;
        pointer-events: auto;
        white-space: nowrap;
        user-select: none;
        z-index: 10;
        line-height: 1.4;
        background: rgba(255,230,0,0.25);
        color: #000;
        padding: 0 2px;
        border: none;
    }
    .mapping-marker:hover { background: rgba(255,230,0,0.45); }
    .mapping-marker:active { cursor: grabbing; }
    .mapping-marker.preview-mode {
        background: transparent;
        border: none;
        color: #000;
        padding: 0;
        cursor: default;
    }
    .mapping-marker.preview-mode:hover { background: transparent; }
    .mapping-marker.image-marker {
        border: 1px dashed rgba(0,0,0,0.25);
        background: rgba(255,230,0,0.1);
        overflow: hidden;
        padding: 0;
    }
    .mapping-marker.image-marker.preview-mode {
        border: none;
        background: transparent;
    }
    .mapping-marker.image-marker img {
        width: 100%;
        height: auto;
        display: block;
    }

    /* Field chips */
    .field-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .field-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: var(--cream);
        border: 1px solid var(--border);
        border-radius: 999px;
        padding: 0.3rem 0.75rem;
        cursor: grab;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text-dark);
        user-select: none;
        transition: all 0.15s;
    }
    .field-chip:hover {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
    }
    .field-chip:active { cursor: grabbing; opacity: 0.7; }
    .field-chip.mapped {
        background: var(--primary-muted);
        border-color: rgba(42,139,146,0.25);
        color: var(--primary);
        opacity: 0.6;
        cursor: default;
    }

    /* Mapping list */
    .mapping-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .mapping-item {
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 0.7rem 0.85rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        background: #fff;
        transition: border-color 0.15s;
    }
    .mapping-item:hover { border-color: var(--primary); }
    .mapping-item__info { flex: 1; min-width: 0; }
    .mapping-item__label {
        font-weight: 600;
        font-size: 0.82rem;
        color: var(--text-dark);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .mapping-item__key {
        font-size: 0.72rem;
        color: var(--text-light);
    }
    .mapping-item__actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
    }
    .mapping-width {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.78rem;
        color: var(--text-mid);
        font-weight: 500;
    }
    .mapping-width input { width: 65px; }

    /* Font size control */
    .font-size-control {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-dark);
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 0.35rem 0.75rem;
    }
    .font-size-control label { margin: 0; white-space: nowrap; }
    .font-size-control input {
        width: 100px;
        text-align: center;
        font-weight: 600;
    }
    .font-size-control span { color: var(--text-light); font-size: 0.78rem; }
</style>
@endsection

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-0">Mappings: {{ ucwords(str_replace('_', ' ', $template->contract_type)) }} <small class="text-muted" style="font-size:0.6em;">({{ strtoupper($template->language) }})</small></h3>
        <div class="page-header-actions">
            <div class="btn-group mode-toggle">
                <button id="btn-edit" class="btn btn-sm btn-primary active">Edit</button>
                <button id="btn-preview" class="btn btn-sm btn-outline-success">Preview</button>
            </div>
            <a href="{{ route('templates.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="mapping-layout">
        {{-- Left: PDF Canvas --}}
        <div class="left-panel">
            <div class="section-card">
                <div class="section-card-header">
                    <div class="d-flex align-items-center gap-3">
                        <span><i class="bi bi-file-earmark-pdf"></i>PDF Preview</span>
                        <div class="font-size-control">
                            <label>Font Size</label>
                            <input type="number" id="fontSizeInput" class="form-control form-control-sm" value="10" min="4" max="24" step="0.5">
                            <span>px</span>
                        </div>
                    </div>
                    <div class="page-nav">
                        <button id="prev-page" class="btn btn-sm btn-outline-secondary" disabled><i class="bi bi-chevron-left"></i></button>
                        <span id="page-info">Page 1 / 1</span>
                        <button id="next-page" class="btn btn-sm btn-outline-secondary" disabled><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
                <div class="section-card-body" id="pdf-container"
                     ondragover="event.preventDefault()" ondrop="handleDrop(event)">
                    <div id="pdf-wrapper">
                        <canvas id="pdf-canvas"></canvas>
                        <div id="marker-overlay"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Fields + Mappings --}}
        <div class="right-panel">
            {{-- Draggable Fields --}}
            <div class="section-card">
                <div class="section-card-header">
                    <span><i class="bi bi-collection"></i>Fields</span>
                    <small class="text-muted fw-normal" style="font-size:0.75rem;">Drag onto PDF</small>
                </div>
                <div class="section-card-body">
                    <div id="field-chips" class="field-chips">
                        @foreach($dbFields as $field)
                            <span class="field-chip" draggable="true"
                                  data-field="{{ $field }}"
                                  data-field-type="{{ $fieldMeta[$field] ?? 'text' }}"
                                  ondragstart="dragStart(event)">
                                {{ $fieldLabels[$field] ?? Str::headline($field) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Existing Mappings --}}
            <div class="section-card">
                <div class="section-card-header">
                    <span><i class="bi bi-link-45deg"></i>Mappings</span>
                    <span class="badge" id="mapping-count">{{ $template->mappings->count() }}</span>
                </div>
                <div class="section-card-body">
                    <div id="mapping-list" class="mapping-list">
                        @foreach($template->mappings as $mapping)
                            <div class="mapping-item" data-mapping-id="{{ $mapping->id }}">
                                <div class="mapping-item__info">
                                    <div class="mapping-item__label">{{ $fieldLabels[$mapping->db_field] ?? Str::headline($mapping->db_field) }}</div>
                                    <div class="mapping-item__key">{{ $mapping->db_field }}</div>
                                </div>
                                <div class="mapping-item__actions">
                                    @if($mapping->field_type === 'image')
                                        <label class="mapping-width mb-0">
                                            <span>W</span>
                                            <input type="number" class="form-control form-control-sm img-width-input"
                                                   value="{{ $mapping->img_width ?? 50 }}" min="1" max="300"
                                                   data-mapping-id="{{ $mapping->id }}">
                                            <span>mm</span>
                                        </label>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteMapping({{ $mapping->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p id="no-mappings" class="text-muted small mb-0 text-center py-2" style="{{ $template->mappings->count() ? 'display:none;' : '' }}">No mappings yet. Drag fields onto the PDF.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
(function() {
    // ── Config ──
    const pdfUrl = "{{ '/storage/' . ltrim($template->file_path, '/') }}";
    const templateId = {{ $template->id }};
    const csrfToken = '{{ csrf_token() }}';
    const storeUrl = '{{ route("templates.mappings.store", $template) }}';
    const baseUpdateUrl = '/templates/' + templateId + '/mappings/';
    const baseDeleteUrl = '/templates/' + templateId + '/mappings/';

    const sampleData = @json($sampleData);
    const fieldLabels = @json($fieldLabels);

    // ── State ──
    let pdfDoc = null;
    let currentPage = 1;
    const BASE_SCALE = 96 / 72; // match screen DPI (96) to PDF DPI (72)
    let scale = BASE_SCALE;
    let isPreview = false;
    let markerFontSize = 10;

    let mappings = @json($template->mappings->toArray());
    const fieldMeta = @json($fieldMeta);

    // ── DOM refs ──
    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');
    const overlay = document.getElementById('marker-overlay');
    const pdfWrapper = document.getElementById('pdf-wrapper');
    const pdfContainer = document.getElementById('pdf-container');
    const fontSizeInput = document.getElementById('fontSizeInput');

    // ── Font size control ──
    fontSizeInput.addEventListener('change', function() {
        markerFontSize = parseFloat(this.value) || 10;
        document.documentElement.style.setProperty('--marker-font-size', markerFontSize + 'px');
        renderMarkers();
    });

    // ── PDF Rendering ──
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
        pdfDoc = pdf;
        renderPage(currentPage);
    }).catch(function() {
        document.getElementById('pdf-container').innerHTML =
            '<p class="text-danger p-3">Failed to load PDF. Make sure storage is linked.</p>';
    });

    function renderPage(num) {
        pdfDoc.getPage(num).then(function(page) {
            const viewport = page.getViewport({ scale: scale });
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            // Force canvas to display at exact buffer size (prevents CSS scaling)
            canvas.style.width = viewport.width + 'px';
            canvas.style.height = viewport.height + 'px';
            pdfWrapper.style.width = viewport.width + 'px';
            pdfWrapper.style.height = viewport.height + 'px';

            page.render({ canvasContext: ctx, viewport: viewport }).promise.then(function() {
                renderMarkers();
            });

            document.getElementById('page-info').textContent = 'Page ' + num + ' / ' + pdfDoc.numPages;
            document.getElementById('prev-page').disabled = (num <= 1);
            document.getElementById('next-page').disabled = (num >= pdfDoc.numPages);
        });
    }

    document.getElementById('prev-page').addEventListener('click', function() {
        if (currentPage > 1) { currentPage--; renderPage(currentPage); }
    });
    document.getElementById('next-page').addEventListener('click', function() {
        if (pdfDoc && currentPage < pdfDoc.numPages) { currentPage++; renderPage(currentPage); }
    });

    // ── Display ratio: accounts for any CSS scaling of the canvas ──
    function getDisplayRatio() {
        const rect = canvas.getBoundingClientRect();
        if (!rect.width || !canvas.width) return 1;
        return rect.width / canvas.width;
    }

    // ── Markers ──
    function renderMarkers() {
        overlay.innerHTML = '';
        mappings.forEach(function(m) {
            if (m.page_number !== currentPage) return;
            createMarkerEl(m);
        });
    }

    function mmToPx(mm) {
        return (parseFloat(mm) || 50) * (72 / 25.4) * scale;
    }

    function createMarkerEl(m) {
        const el = document.createElement('div');
        const isImageField = m.field_type === 'image';
        el.className = 'mapping-marker' + (isPreview ? ' preview-mode' : '') + (isImageField ? ' image-marker' : '');
        el.dataset.mappingId  = m.id;
        el.dataset.fieldType  = m.field_type;
        el.style.fontSize = markerFontSize + 'px';

        const dr = getDisplayRatio();

        if (isImageField) {
            const wPx = mmToPx(m.img_width || 50) * dr;
            el.style.width  = wPx + 'px';
            el.dataset.wPx  = wPx;
            el.style.display  = 'block';
            el.style.overflow = 'hidden';
            el.style.padding  = '0';

            const imageSrc = sampleData[m.db_field] || '';
            if (imageSrc) {
                const img = document.createElement('img');
                img.src = '/storage/' + imageSrc;
                img.style.display = 'block';
                img.style.width = '100%';
                img.style.height = 'auto';
                img.onload = function() { el.dataset.hPx = img.offsetHeight; };
                el.appendChild(img);
            } else {
                el.style.display = 'flex';
                el.style.alignItems = 'center';
                el.style.justifyContent = 'center';
                el.style.height = Math.round(wPx * 2 / 3) + 'px';
                el.textContent = (m.img_width || 50) + 'mm';
            }

            el.style.left = (m.x_position * scale * dr) + 'px';
            el.style.top  = (m.y_position * scale * dr) + 'px';
        } else {
            el.textContent = isPreview ? (sampleData[m.db_field] || m.db_field) : (fieldLabels[m.db_field] || m.db_field);
            el.style.left = (m.x_position * scale * dr) + 'px';
            el.style.top  = (m.y_position * scale * dr) + 'px';
        }

        if (!isPreview) {
            makeDraggable(el, m);
        }

        overlay.appendChild(el);
    }

    // ── Drag markers to reposition ──
    function makeDraggable(el, mapping) {
        let startX, startY, origLeft, origTop;

        el.addEventListener('mousedown', function(e) {
            e.preventDefault();
            startX = e.clientX;
            startY = e.clientY;
            origLeft = parseFloat(el.style.left);
            origTop = parseFloat(el.style.top);

            function onMove(e2) {
                const dx = e2.clientX - startX;
                const dy = e2.clientY - startY;
                el.style.left = (origLeft + dx) + 'px';
                el.style.top = (origTop + dy) + 'px';
            }

            function onUp() {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);

                const dr = getDisplayRatio();
                const newLeft = parseFloat(el.style.left);
                const newTop  = parseFloat(el.style.top);
                const maxLeft = canvas.width * dr - 20;
                const maxTop  = canvas.height * dr - 10;
                const clampedLeft = Math.max(0, Math.min(newLeft, maxLeft));
                const clampedTop  = Math.max(0, Math.min(newTop,  maxTop));

                el.style.left = clampedLeft + 'px';
                el.style.top  = clampedTop  + 'px';

                const pdfX = clampedLeft / (scale * dr);
                const pdfY = clampedTop  / (scale * dr);

                fetch(baseUpdateUrl + mapping.id, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        x_position: Math.round(pdfX * 100) / 100,
                        y_position: Math.round(pdfY * 100) / 100,
                        page_number: currentPage
                    })
                })
                .then(r => r.json())
                .then(function(data) {
                    if (data.success) {
                        mapping.x_position = data.mapping.x_position;
                        mapping.y_position = data.mapping.y_position;
                    }
                });
            }

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });
    }

    // ── Drag field chip onto PDF ──
    window.dragStart = function(e) {
        const chip = e.target;
        if (chip.classList.contains('mapped')) { e.preventDefault(); return; }
        e.dataTransfer.setData('text/plain', chip.dataset.field);
    };

    window.handleDrop = function(e) {
        e.preventDefault();
        if (isPreview) return;

        const field = e.dataTransfer.getData('text/plain');
        if (!field) return;

        const fieldType = fieldMeta[field] || 'text';
        const wrapperRect = pdfWrapper.getBoundingClientRect();
        const dropX = e.clientX - wrapperRect.left;
        const dropY = e.clientY - wrapperRect.top;
        const dr = getDisplayRatio();
        const pdfX = dropX / (scale * dr);
        const pdfY = dropY / (scale * dr);

        fetch(storeUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                db_field: field,
                field_type: fieldType,
                x_position: Math.round(pdfX * 100) / 100,
                y_position: Math.round(pdfY * 100) / 100,
                page_number: currentPage,
                img_width: fieldType === 'image' ? 50 : null,
            })
        })
        .then(r => r.json())
        .then(function(data) {
            if (data.success) {
                mappings.push(data.mapping);
                createMarkerEl(data.mapping);
                addMappingCard(data.mapping);
                updateChipStates();
                updateMappingCount();
            }
        });
    };

    // ── Delete ──
    window.deleteMapping = function(id) {
        if (!confirm('Delete this mapping?')) return;

        fetch(baseDeleteUrl + id, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(function(data) {
            if (data.success) {
                mappings = mappings.filter(m => m.id !== id);
                const marker = overlay.querySelector('[data-mapping-id="' + id + '"]');
                if (marker) marker.remove();
                const card = document.querySelector('#mapping-list .mapping-item[data-mapping-id="' + id + '"]');
                if (card) card.remove();
                updateChipStates();
                updateMappingCount();
            }
        });
    };

    // ── Mapping list helpers ──
    function addMappingCard(m) {
        const list = document.getElementById('mapping-list');
        const card = document.createElement('div');
        card.className = 'mapping-item';
        card.dataset.mappingId = m.id;

        const label = fieldLabels[m.db_field] || m.db_field.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        const info = document.createElement('div');
        info.className = 'mapping-item__info';
        info.innerHTML = '<div class="mapping-item__label">' + escHtml(label) + '</div>' +
            '<div class="mapping-item__key">' + escHtml(m.db_field) + '</div>';

        const actions = document.createElement('div');
        actions.className = 'mapping-item__actions';

        if (m.field_type === 'image') {
            const widthLabel = document.createElement('label');
            widthLabel.className = 'mapping-width mb-0';
            widthLabel.innerHTML = '<span>W</span>';

            const input = document.createElement('input');
            input.type = 'number';
            input.className = 'form-control form-control-sm img-width-input';
            input.min = '1';
            input.max = '300';
            input.value = m.img_width || 50;
            input.dataset.mappingId = m.id;

            const unit = document.createElement('span');
            unit.textContent = 'mm';

            widthLabel.appendChild(input);
            widthLabel.appendChild(unit);
            actions.appendChild(widthLabel);
            attachWidthListener(input);
        }

        const delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'btn btn-sm btn-outline-danger';
        delBtn.innerHTML = '<i class="bi bi-trash"></i>';
        delBtn.addEventListener('click', function() { deleteMapping(m.id); });
        actions.appendChild(delBtn);

        card.appendChild(info);
        card.appendChild(actions);
        list.appendChild(card);

        document.getElementById('no-mappings').style.display = 'none';
    }

    function attachWidthListener(inputEl) {
        if (!inputEl) return;
        inputEl.addEventListener('change', function() {
            const mappingId = this.dataset.mappingId;
            const width = parseFloat(this.value);
            if (!width || width < 1) return;
            const m = mappings.find(x => x.id == mappingId);
            if (!m) return;

            const marker = overlay.querySelector('[data-mapping-id="' + mappingId + '"]');
            if (marker) {
                const wPx = mmToPx(width) * getDisplayRatio();
                marker.style.width = wPx + 'px';
                marker.dataset.wPx = wPx;
                const innerImg = marker.querySelector('img');
                if (innerImg) {
                    innerImg.style.width = '100%';
                } else {
                    marker.textContent = width + 'mm';
                }
            }

            fetch(baseUpdateUrl + mappingId, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    x_position: m.x_position,
                    y_position: m.y_position,
                    page_number: m.page_number,
                    img_width: width,
                })
            }).then(r => r.json()).then(data => {
                if (data.success) { m.img_width = data.mapping.img_width; }
            });
        });
    }

    document.querySelectorAll('#mapping-list .img-width-input').forEach(attachWidthListener);

    function updateMappingCount() {
        document.getElementById('mapping-count').textContent = mappings.length;
        document.getElementById('no-mappings').style.display = mappings.length ? 'none' : '';
    }

    // ── Chip states ──
    function updateChipStates() {
        const mappedFields = mappings.map(m => m.db_field);
        document.querySelectorAll('.field-chip').forEach(function(chip) {
            if (mappedFields.includes(chip.dataset.field)) {
                chip.classList.add('mapped');
                chip.draggable = false;
            } else {
                chip.classList.remove('mapped');
                chip.draggable = true;
            }
        });
    }

    // ── Preview toggle ──
    document.getElementById('btn-edit').addEventListener('click', function() {
        isPreview = false;
        this.classList.add('active', 'btn-primary');
        this.classList.remove('btn-outline-primary');
        document.getElementById('btn-preview').classList.remove('active', 'btn-success');
        document.getElementById('btn-preview').classList.add('btn-outline-success');
        renderMarkers();
    });

    document.getElementById('btn-preview').addEventListener('click', function() {
        isPreview = true;
        this.classList.add('active', 'btn-success');
        this.classList.remove('btn-outline-success');
        document.getElementById('btn-edit').classList.remove('active', 'btn-primary');
        document.getElementById('btn-edit').classList.add('btn-outline-primary');
        renderMarkers();
    });

    // ── Util ──
    function escHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ── Init ──
    updateChipStates();
})();
</script>
@endsection
