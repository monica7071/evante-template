@extends('layouts.app')

@section('title', 'Preview Quotation - ' . ($listing->unit_code ?? $listing->room_number))

@section('styles')
<style>
    #pdf-wrapper { position: relative; display: inline-block; }
    #pdf-canvas { display: block; border: none; border-radius: 4px; box-shadow: 0 4px 24px rgba(0,0,0,0.1); max-width: none !important; }
    #pdf-container { overflow: auto; background: var(--cream-dark); border-radius: 0 0 var(--radius) var(--radius); padding: 24px !important; }
    #marker-overlay {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        pointer-events: none;
    }
    .data-label {
        position: absolute;
        color: #000;
        font-size: 12px;
        white-space: nowrap;
        pointer-events: none;
        line-height: 1.3;
    }
    .data-label img {
        width: 100%;
        height: auto;
        display: block;
    }
</style>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="mb-0">Preview: Quotation ({{ strtoupper($language) }})</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('contracts.quotation.download-listing', [$listing->id, $language]) }}" class="btn btn-primary">Download PDF</a>
            <a href="{{ route('contracts.quotation.index') }}" class="btn btn-outline-secondary">Back to Listings</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>{{ $listing->unit_code ?? $listing->room_number ?? '-' }} — {{ $listing->project->name ?? '-' }}</span>
            <div>
                <button id="prev-page" class="btn btn-sm btn-outline-secondary" disabled>&laquo; Prev</button>
                <span id="page-info" class="mx-2">Page 1 / 1</span>
                <button id="next-page" class="btn btn-sm btn-outline-secondary" disabled>Next &raquo;</button>
            </div>
        </div>
        <div class="card-body text-center p-2" id="pdf-container">
            <div id="pdf-wrapper">
                <canvas id="pdf-canvas"></canvas>
                <div id="marker-overlay"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
(function() {
    const pdfUrl = "{{ '/storage/' . ltrim($template->file_path, '/') }}";
    const mappings = @json($template->mappings->toArray());
    const contractData = @json($contractData);

    let pdfDoc = null;
    let currentPage = 1;
    const BASE_SCALE = 96 / 72; // match screen DPI (96) to PDF DPI (72)
    let scale = BASE_SCALE;

    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');
    const overlay = document.getElementById('marker-overlay');
    const pdfWrapper = document.getElementById('pdf-wrapper');
    const pdfContainer = document.getElementById('pdf-container');

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
        pdfDoc = pdf;
        renderPage(currentPage);
    }).catch(function() {
        pdfContainer.innerHTML = '<p class="text-danger p-3">Failed to load PDF.</p>';
    });

    function renderPage(num) {
        pdfDoc.getPage(num).then(function(page) {
            const viewport = page.getViewport({ scale: scale });
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            canvas.style.width = viewport.width + 'px';
            canvas.style.height = viewport.height + 'px';
            pdfWrapper.style.width = viewport.width + 'px';
            pdfWrapper.style.height = viewport.height + 'px';

            page.render({ canvasContext: ctx, viewport: viewport }).promise.then(renderData);

            document.getElementById('page-info').textContent = 'Page ' + num + ' / ' + pdfDoc.numPages;
            document.getElementById('prev-page').disabled = (num <= 1);
            document.getElementById('next-page').disabled = (num >= pdfDoc.numPages);
        });
    }

    function mmToPx(mm) {
        return (parseFloat(mm) || 50) * (72 / 25.4) * scale;
    }

    function renderData() {
        overlay.innerHTML = '';
        mappings.forEach(function(m) {
            if (m.page_number !== currentPage) return;
            const value = contractData[m.db_field];
            if (value === undefined || value === null || value === '') return;

            const el = document.createElement('div');
            el.className = 'data-label';
            if (m.field_type === 'image') {
                const wPx = mmToPx(m.img_width || 50);
                el.style.width = wPx + 'px';
                el.style.overflow = 'hidden';
                const img = document.createElement('img');
                img.src = '/storage/' + value;
                img.style.width = '100%';
                img.style.height = 'auto';
                img.style.display = 'block';
                el.appendChild(img);
            } else {
                el.textContent = value;
            }
            el.style.left = (m.x_position * scale) + 'px';
            el.style.top = (m.y_position * scale) + 'px';
            overlay.appendChild(el);
        });
    }

    document.getElementById('prev-page').addEventListener('click', function() {
        if (currentPage > 1) { currentPage--; renderPage(currentPage); }
    });
    document.getElementById('next-page').addEventListener('click', function() {
        if (pdfDoc && currentPage < pdfDoc.numPages) { currentPage++; renderPage(currentPage); }
    });
})();
</script>
@endsection
