@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<div class="contract-preview-container" data-pdf-url="{{ $pdfUrl }}" data-download-url="{{ $downloadUrl }}">
    <div class="preview-header">
        <div class="header-content">
            <div class="header-actions-left">
                <a href="{{ route('buy-sale.index') }}" class="header-btn back-btn">
                    ← Back to Sale
                </a>
            </div>
            <div class="header-actions-right">
                <button id="downloadPdf" class="header-btn primary">
                    <i class="bi bi-cloud-arrow-down"></i>
                    Download PDF
                </button>
            </div>
        </div>
    </div>

    <div class="signature-toolbar">
        <div class="signature-actions">
            <a href="#" class="signature-btn">Tenant Sign</a>
            <a href="#" class="signature-btn">Witness 1 Sign</a>
            <a href="#" class="signature-btn">Witness 2 Sign</a>
        </div>
    </div>

    <div class="pdf-viewer-card">
        <div class="pdf-viewer" id="pdfViewer">
            <div class="pdf-loading" id="pdfLoading">
                <div class="loading-spinner"></div>
                <p>Loading document…</p>
            </div>
            <canvas id="pdfCanvas" class="pdf-canvas" aria-label="Contract preview" role="img"></canvas>
        </div>
        <div class="pdf-controls">
            <button id="prevPage" class="control-btn" disabled>
                <i class="bi bi-chevron-left"></i>
            </button>
            <span class="page-info"><span id="currentPage">1</span> / <span id="totalPages">1</span></span>
            <button id="nextPage" class="control-btn" disabled>
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .contract-preview-container {
        padding-bottom: 48px;
    }
    .preview-header {
        border-bottom: 1px solid var(--border);
        background: var(--surface);
    }
    .header-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .header-actions-left {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }
    .header-actions-right {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .header-btn {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 14px;
        display: inline-flex;
        gap: 8px;
        align-items: center;
        text-decoration: none;
    }
    .header-btn.primary { background: var(--primary); color: #fff; border-color: var(--primary); }
    .header-btn.secondary { background: #f3f4f6; color: #4b5563; }
    .header-btn.back-btn { background: #fff; color: #111827; }
    .header-btn:disabled { opacity: 0.6; cursor: not-allowed; }

    .contract-tags {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .tag {
        background: #f3f4f6;
        color: #4b5563;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 13px;
    }
    .tag.status { background: #ecfeff; color: #0e7490; }

    .signature-toolbar {
        max-width: 1200px;
        margin: 20px auto 0;
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    .signature-title { font-weight: 600; color: #111827; }
    .signature-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .signature-btn {
        background: #fff;
        border: 1px dashed #d97706;
        color: #b45309;
        padding: 6px 14px;
        border-radius: 999px;
        text-decoration: none;
        font-size: 13px;
    }
    .signature-note { font-size: 12px; color: #6b7280; }

    .contract-summary {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 24px 12px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }
    .summary-label { font-size: 12px; text-transform: uppercase; color: #9ca3af; margin-bottom: 4px; }
    .summary-value { font-size: 15px; color: #111827; font-weight: 600; }

    .pdf-viewer-card {
        max-width: 900px;
        margin: 16px auto 0;
        padding: 0 16px;
    }
    .pdf-viewer {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        min-height: 700px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .pdf-loading { text-align: center; color: #6b7280; }
    .loading-spinner {
        width: 36px;
        height: 36px;
        border: 3px solid #e5e7eb;
        border-top: 3px solid #0f62fe;
        border-radius: 50%;
        margin: 0 auto 12px;
        animation: spin 1s linear infinite;
    }
    @keyframes spin { from { transform: rotate(0deg);} to { transform: rotate(360deg);} }
    .pdf-canvas { display: none; max-width: 100%; box-shadow: 0 12px 30px rgba(15, 98, 254, 0.08); }

    .pdf-controls {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 18px;
        padding: 12px;
        margin-top: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
    }
    .control-btn {
        border: 1px solid #d1d5db;
        border-radius: 50%;
        width: 36px; height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f9fafb;
        color: #111827;
    }
    .control-btn:disabled { opacity: 0.4; }
    .page-info { font-weight: 600; color: #111827; }

    @media (max-width: 768px) {
        .header-content { flex-direction: column; }
        .contract-summary { grid-template-columns: 1fr 1fr; }
    }
</style>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    document.addEventListener('DOMContentLoaded', () => {
        const container = document.querySelector('.contract-preview-container');
        if (!container) return;

        const pdfUrl = container.dataset.pdfUrl;
        const downloadUrl = container.dataset.downloadUrl;
        const pdfCanvas = document.getElementById('pdfCanvas');
        const ctx = pdfCanvas.getContext('2d');
        const loading = document.getElementById('pdfLoading');
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        const currentPageEl = document.getElementById('currentPage');
        const totalPagesEl = document.getElementById('totalPages');
        const downloadBtn = document.getElementById('downloadPdf');

        let pdfDoc = null;
        let pageNum = 1;
        let pageRendering = false;
        let pageNumPending = null;
        const scale = 1.4;

        function updateControls() {
            if (!pdfDoc) return;
            prevBtn.disabled = pageNum <= 1;
            nextBtn.disabled = pageNum >= pdfDoc.numPages;
            currentPageEl.textContent = pageNum;
            totalPagesEl.textContent = pdfDoc.numPages;
        }

        function renderPage(num) {
            pageRendering = true;
            pdfDoc.getPage(num).then(page => {
                const viewport = page.getViewport({ scale });
                pdfCanvas.height = viewport.height;
                pdfCanvas.width = viewport.width;
                return page.render({ canvasContext: ctx, viewport }).promise;
            }).then(() => {
                pageRendering = false;
                if (pageNumPending !== null) {
                    renderPage(pageNumPending);
                    pageNumPending = null;
                }
                updateControls();
            });
        }

        function queueRenderPage(num) {
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
        }

        prevBtn.addEventListener('click', () => {
            if (pageNum <= 1) return;
            pageNum -= 1;
            queueRenderPage(pageNum);
        });

        nextBtn.addEventListener('click', () => {
            if (pageNum >= pdfDoc.numPages) return;
            pageNum += 1;
            queueRenderPage(pageNum);
        });

        downloadBtn.addEventListener('click', () => {
            window.open(downloadUrl, '_blank');
        });

        pdfjsLib.getDocument({
            url: pdfUrl,
            withCredentials: true,
            httpHeaders: { 'Accept': 'application/pdf' }
        }).promise
            .then(pdf => {
                pdfDoc = pdf;
                loading.style.display = 'none';
                pdfCanvas.style.display = 'block';
                renderPage(pageNum);
            })
            .catch((error) => {
                console.error('PDF preview error', error);
                loading.innerHTML = '<p class="text-danger">Unable to load PDF preview. Please check the template.</p>';
            });
    });
</script>
@endsection
