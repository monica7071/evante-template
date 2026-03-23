@extends('layouts.app')

@section('title', 'Preview Contract - ' . $contract->buyer_name)

@section('styles')
<style>
    #pdf-wrapper {
        position: relative;
        display: inline-block;
    }
    #pdf-canvas {
        display: block;
        border: 1px solid #dee2e6;
    }
    #pdf-container {
        overflow: auto;
    }
    #marker-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
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
        <h2 class="mb-0">Preview: {{ ucfirst($contract->type) }} Contract</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('contracts.download', $contract) }}" class="btn btn-primary">Download PDF</a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>{{ $contract->buyer_name }} — {{ ucfirst($contract->type) }}</span>
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

    <div class="card">
        <div class="card-header">Contract Details</div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3"><strong>Buyer:</strong> {{ $contract->buyer_name }}</div>
                <div class="col-md-3"><strong>ID:</strong> {{ $contract->id_number }}</div>
                <div class="col-md-3"><strong>Phone:</strong> {{ $contract->phone }}</div>
                <div class="col-md-3"><strong>Email:</strong> {{ $contract->email ?? '-' }}</div>
                <div class="col-md-3"><strong>Unit:</strong> {{ $contract->unit_number }}</div>
                <div class="col-md-3"><strong>Price:</strong> {{ number_format($contract->price, 2) }}</div>
                <div class="col-md-3"><strong>Deposit:</strong> {{ $contract->deposit ? number_format($contract->deposit, 2) : '-' }}</div>
                <div class="col-md-3"><strong>Date:</strong> {{ $contract->contract_date->format('Y-m-d') }}</div>
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
    const BASE_SCALE = 1;
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
            pdfWrapper.style.width = viewport.width + 'px';
            pdfWrapper.style.height = viewport.height + 'px';

            page.render({ canvasContext: ctx, viewport: viewport }).promise.then(function() {
                renderData();
            });

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
            if (!value && value !== 0) return;

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
