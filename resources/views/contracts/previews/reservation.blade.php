@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<div class="cpv-wrapper" data-pdf-url="{{ $pdfUrl }}" data-download-url="{{ $downloadUrl }}">

    {{-- ── Document Title ── --}}
    <div class="cpv-doc-title">{{ $pageTitle }}</div>

    {{-- ── Top Bar ── --}}
    <div class="cpv-topbar">
        <div class="cpv-topbar-inner">
            <a href="{{ route('buy-sale.index') }}" class="cpv-back">
                <i class="bi bi-arrow-left"></i> Back to Sale
            </a>
            <div class="cpv-topbar-actions">
                @if(isset($contractType) && $contractType === 'reservation_agreement')
                    @php
                        $sigTypes = [
                            'buyer'    => ['label' => 'Buyer', 'icon' => 'bi-person-check', 'name' => $reservation->buyer_signature_name ?? null, 'signed' => $reservation->buyer_signed_at ?? null],
                            'witness1' => ['label' => 'Witness 1', 'icon' => 'bi-person', 'name' => $reservation->witness_one_name ?? null, 'signed' => $reservation->witness_one_signed_at ?? null],
                            'witness2' => ['label' => 'Witness 2', 'icon' => 'bi-person', 'name' => $reservation->witness_two_name ?? null, 'signed' => $reservation->witness_two_signed_at ?? null],
                        ];
                    @endphp
                    @foreach($sigTypes as $type => $sig)
                        <a href="{{ route('contracts.reservation-agreement.signature', ['sale' => $sale->id, 'type' => $type]) }}"
                           target="_blank"
                           class="cpv-btn cpv-btn-sig {{ $sig['signed'] ? 'signed' : '' }}"
                           title="{{ $sig['signed'] ? $sig['name'] . ' · ' . \Carbon\Carbon::parse($sig['signed'])->timezone('Asia/Bangkok')->format('d/m/Y H:i') : 'Not signed yet' }}">
                            <i class="bi {{ $sig['signed'] ? 'bi-check-circle-fill' : 'bi-pen' }}"></i>
                            {{ $sig['label'] }}
                        </a>
                    @endforeach
                @endif
                <button id="downloadPdf" class="cpv-btn cpv-btn-download">
                    <i class="bi bi-download"></i> Download
                </button>
            </div>
        </div>
    </div>

    {{-- ── PDF Viewer ── --}}
    <div class="cpv-viewer-wrap">
        <div class="cpv-viewer" id="pdfViewer">
            <div class="cpv-loading" id="pdfLoading">
                <div class="cpv-spinner"></div>
                <p>Loading document...</p>
            </div>
            <canvas id="pdfCanvas" class="cpv-canvas" aria-label="Contract preview" role="img"></canvas>
        </div>
        <div class="cpv-pager">
            <button id="prevPage" class="cpv-pager-btn" disabled>
                <i class="bi bi-chevron-left"></i>
            </button>
            <span class="cpv-pager-info">
                <span id="currentPage">1</span> / <span id="totalPages">1</span>
            </span>
            <button id="nextPage" class="cpv-pager-btn" disabled>
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* ── Wrapper ── */
    .cpv-wrapper { padding-bottom: 48px; }

    /* ── Top Bar ── */
    .cpv-topbar {
        border-bottom: 1px solid #e5e7eb;
        position: sticky;
        top: 0;
        z-index: 20;
    }
    .cpv-topbar-inner {
        max-width: 1100px;
        margin: 0 auto;
        padding: 14px 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .cpv-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 500;
        color: #6b7280;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.15s;
    }

    /* ── Document Title ── */
    .cpv-doc-title {
        max-width: 1100px;
        margin: 0 auto;
        padding: 16px 0;
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
    }
    .cpv-topbar-actions { display: flex; gap: 8px; }
    .cpv-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.15s;
    }
    .cpv-btn-download {
        background: #667777;
        color: #fff;
    }
    .cpv-btn-download:hover { background: #4a5858; }

    /* ── Signature Buttons ── */
    .cpv-btn-sig {
        background: #fff;
        border: 1px dashed #d97706;
        color: #b45309;
        text-decoration: none;
        padding: 8px 14px;
    }
    .cpv-btn-sig:hover {
        border-style: solid;
        background: #fffbeb;
        color: #92400e;
    }
    .cpv-btn-sig.signed {
        border-color: #16a34a;
        border-style: solid;
        background: #f0fdf4;
        color: #16a34a;
    }
    .cpv-btn-sig.signed:hover {
        background: #dcfce7;
    }

    /* ── PDF Viewer ── */
    .cpv-viewer-wrap {
        max-width: 900px;
        margin: 20px auto 0;
        padding: 0 24px;
    }
    .cpv-viewer {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        min-height: 700px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    }
    .cpv-loading { text-align: center; color: #6b7280; }
    .cpv-spinner {
        width: 36px;
        height: 36px;
        border: 3px solid #e5e7eb;
        border-top: 3px solid #667777;
        border-radius: 50%;
        margin: 0 auto 12px;
        animation: cpv-spin 0.8s linear infinite;
    }
    @keyframes cpv-spin { to { transform: rotate(360deg); } }
    .cpv-canvas {
        display: none;
        max-width: 100%;
        border-radius: 4px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    }

    /* ── Pager ── */
    .cpv-pager {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 16px;
        padding: 12px;
        margin-top: 12px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }
    .cpv-pager-btn {
        border: 1px solid #d1d5db;
        border-radius: 50%;
        width: 34px; height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f9fafb;
        color: #374151;
        cursor: pointer;
        transition: all 0.15s;
    }
    .cpv-pager-btn:hover:not(:disabled) { background: #e5e7eb; }
    .cpv-pager-btn:disabled { opacity: 0.35; cursor: default; }
    .cpv-pager-info {
        font-weight: 600;
        font-size: 14px;
        color: #1e293b;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .cpv-topbar-inner { flex-wrap: wrap; }
        .cpv-topbar-actions { flex-wrap: wrap; }
    }
</style>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    document.addEventListener('DOMContentLoaded', () => {
        const wrapper = document.querySelector('.cpv-wrapper');
        if (!wrapper) return;

        const pdfUrl      = wrapper.dataset.pdfUrl;
        const downloadUrl = wrapper.dataset.downloadUrl;
        const canvas      = document.getElementById('pdfCanvas');
        const ctx         = canvas.getContext('2d');
        const loading     = document.getElementById('pdfLoading');
        const prevBtn     = document.getElementById('prevPage');
        const nextBtn     = document.getElementById('nextPage');
        const curPageEl   = document.getElementById('currentPage');
        const totalEl     = document.getElementById('totalPages');
        const dlBtn       = document.getElementById('downloadPdf');

        let pdfDoc = null, pageNum = 1, rendering = false, pending = null;
        const scale = 1.4;

        function updateUI() {
            if (!pdfDoc) return;
            prevBtn.disabled = pageNum <= 1;
            nextBtn.disabled = pageNum >= pdfDoc.numPages;
            curPageEl.textContent = pageNum;
            totalEl.textContent   = pdfDoc.numPages;
        }

        function renderPage(num) {
            rendering = true;
            pdfDoc.getPage(num).then(page => {
                const vp = page.getViewport({ scale });
                canvas.height = vp.height;
                canvas.width  = vp.width;
                return page.render({ canvasContext: ctx, viewport: vp }).promise;
            }).then(() => {
                rendering = false;
                if (pending !== null) { renderPage(pending); pending = null; }
                updateUI();
            });
        }

        function queue(num) { rendering ? (pending = num) : renderPage(num); }

        prevBtn.addEventListener('click', () => { if (pageNum > 1) queue(--pageNum); });
        nextBtn.addEventListener('click', () => { if (pdfDoc && pageNum < pdfDoc.numPages) queue(++pageNum); });
        dlBtn.addEventListener('click', () => window.open(downloadUrl, '_blank'));

        pdfjsLib.getDocument({ url: pdfUrl, withCredentials: true, httpHeaders: { 'Accept': 'application/pdf' } })
            .promise
            .then(pdf => {
                pdfDoc = pdf;
                loading.style.display = 'none';
                canvas.style.display  = 'block';
                renderPage(pageNum);
            })
            .catch(err => {
                console.error('PDF preview error', err);
                loading.innerHTML = '<p style="color:#ef4444;">Unable to load PDF preview. Please check the template.</p>';
            });
    });
</script>
@endsection
