@extends('layouts.app')

@section('title', 'Print Reservation Contract')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="contract-preview-container">
    <div class="preview-header">
        <div class="header-content">
            <div class="header-actions-left">
                <button onclick="previewServiceAgreement()" class="header-btn back-btn" style="background-color: #C19165; color: white;">
                    Service Agreement PDF
                </button>
                <div class="signature-actions-group">
                    <button type="button" class="header-btn signature-action-btn" id="tenantSignatureBtn" data-url="">
                        Tenant Sign
                    </button>
                    <button type="button" class="header-btn signature-action-btn" id="witness1SignatureBtn" data-url="">
                        Witness 1 Sign
                    </button>
                    <button type="button" class="header-btn signature-action-btn" id="witness2SignatureBtn" data-url="">
                        Witness 2 Sign
                    </button>
                    <span class="signature-info tooltip" aria-label="Signature instructions" role="button" tabindex="0">
                        <span class="tooltip-icon" aria-hidden="true">ⓘ</span>
                        <span class="tooltip-text">
                            <strong>Tenant Signature</strong><br>
                            Copy the tenant’s signature link for the Reservation Contract.<br><br>
                            <strong>Witness Signature 1</strong><br>
                            Copy the signature link for Witness 1 of the Reservation Contract.<br><br>
                            <strong>Witness Signature 2</strong><br>
                            Copy the signature link for Witness 2 of the Reservation Contract.
                        </span>
                    </span>
                </div>

                @php
                    $reqStatusRaw = $booking->request->status ?? '';
                    $reqStatus = strtolower(trim((string) $reqStatusRaw));
                @endphp

                @if($reqStatus === 'closing')
                    <button type="button" id="previewRentalAgreementPdfBtn" class="header-btn">
                        Rental Agreement PDF
                    </button>
                @endif
            </div>

            <div class="header-actions-right">
                <div class="header-btn download-btn">
                    <button id="downloadPdf" class="btn btn-primary">
                        <i class="fas fa-download"></i> Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($pdfDebugEnabled ?? false)
    <div class="pdf-debug-panel" id="pdf-debug-panel" aria-live="polite">
        <div>
            <span class="pdf-debug-panel__title">PDF Debug Timeline</span>
            <small class="pdf-debug-panel__timestamp" id="pdf-debug-generated-at">Awaiting request...</small>
        </div>
        <ol class="pdf-debug-events" id="pdf-debug-events">
            <li class="pdf-debug-panel__empty">Run the preview or download to see step timing.</li>
        </ol>
    </div>
    @endif

    <div class="pdf-viewer-container">
        <div class="pdf-content">
            <div class="pdf-loading" id="pdf-loading">
                <div class="loading-spinner"></div>
                <p>Loading Reservation Contract...</p>
            </div>
            <canvas id="pdf-canvas" style="display:none;"></canvas>
        </div>
        
        <div class="pdf-controls">
            <button id="prev-page" class="control-btn">
                <span>←</span>
            </button>
            
            <div class="page-info">
                <span id="current-page">1</span>
                <span class="separator"> / </span>
                <span id="total-pages">1</span>
            </div>
            
            <button id="next-page" class="control-btn">
                <span>→</span>
            </button>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .contract-preview-container {
        min-height: 100vh;
        background-color: #f8f9fa;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .pdf-debug-panel {
        display: none;
        background: #1b1f24;
        color: #f6f8fa;
        border-radius: 10px;
        padding: 16px 20px;
        margin: 16px auto 0;
        max-width: 900px;
        border: 1px solid #30363d;
        box-shadow: 0 12px 30px rgba(5, 10, 20, 0.35);
    }

    .pdf-debug-panel__title {
        font-size: 15px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #79c0ff;
    }

    .pdf-debug-panel__timestamp {
        font-size: 12px;
        color: #8b949e;
        display: block;
    }

    .pdf-debug-events {
        list-style: none;
        padding-left: 0;
        margin: 12px 0 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
        max-height: 240px;
        overflow-y: auto;
    }

    .pdf-debug-event {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 13px;
        line-height: 1.4;
    }

    .pdf-debug-event__header {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        font-weight: 600;
        color: #e4e7eb;
    }

    .pdf-debug-event__meta {
        font-size: 12px;
        color: #9ea7b3;
        margin-top: 2px;
    }

    .pdf-debug-event--error {
        border-color: rgba(255, 99, 132, 0.55);
        background: rgba(255, 99, 132, 0.12);
    }

    .pdf-debug-event--slow {
        border-color: rgba(255, 205, 86, 0.55);
        background: rgba(255, 205, 86, 0.12);
    }

    .pdf-debug-panel__empty {
        color: #9ea7b3;
        font-size: 13px;
    }

    .preview-header {
        background: white;
        border-bottom: 1px solid #e9ecef;
        padding: 15px 0;
    }

    .header-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .signature-actions-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .signature-action-btn {
        background: #fff7ed;
        border-color: #eab308;
        color: #92400e;
    }

    .signature-action-btn:hover {
        background: #fef3c7;
        border-color: #d97706;
        color: #92400e;
    }

    .signature-list {
        list-style: none;
        padding-left: 0;
        margin: 8px 0 0;
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        color: #4b5563;
        font-size: 13px;
    }

    .signature-list li {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #fff;
        border-radius: 999px;
        padding: 6px 12px;
    }

    .signature-list strong {
        font-weight: 600;
        color: #111827;
    }

    .tooltip {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #f3f4f6;
        color: #111827;
        font-size: 11px;
        cursor: default;
        opacity: 1 !important;
        z-index: 1;
    }

    .tooltip-text {
        visibility: hidden;
        position: absolute;
        bottom: 20px;
        left: 140px;
        transform: translate(-50%, 100%);
        background: #111827;
        color: #fff;
        padding: 8px 10px;
        border-radius: 6px;
        font-size: 12px;
        width: 220px;
        line-height: 1.3;
        z-index: 10;
    }

    .tooltip-text::after {
        content: '';
        position: absolute;
        top: -5px;
        left: 50%;
        transform: translateX(-50%);
        border-width: 5px;
        border-style: solid;
        border-color: transparent transparent #111827 transparent;
    }

    .tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }

    .signature-info {
        margin-left: 0;
        width: 24px;
        height: 24px;
        background: #ffff;
        color: #464646ff;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        border-radius: 999px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .signature-info:focus-visible {
        outline: 2px solid #f97316;
        outline-offset: 2px;
    }

    .signature-info:focus-visible .tooltip-text,
    .signature-info:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }

    .tooltip-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        letter-spacing: 0.04em;
        font-size: 15px;
    }

    .header-actions-left {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .header-actions-right {
        display: flex;
        align-items: center;
        margin-left: auto;
    }

    .header-btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid #d0d7de;
        background: #f6f8fa;
        color: #24292f;
    }

    .header-btn:hover {
        background: #f3f4f6;
        border-color: #d0d7de;
    }

    .back-btn:hover {
        background: #f3f4f6;
    }

    .download-btn {
        background: #0969da;
        color: white;
        border-color: #0969da;
    }

    .download-btn:hover {
        background: #0860ca;
        border-color: #0860ca;
    }

    .pdf-viewer-container {
        max-width: 900px;
        margin: 24px auto;
        padding: 0 24px;
    }

    .pdf-content {
        background: white;
        border: 1px solid #d0d7de;
        border-radius: 12px;
        padding: 40px;
        text-align: center;
        min-height: 700px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .pdf-loading {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        color: #656d76;
    }

    .loading-spinner {
        width: 32px;
        height: 32px;
        border: 3px solid #f3f4f6;
        border-top: 3px solid #0969da;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    #pdf-canvas {
        max-width: 100%;
        max-height: 800px;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }

    .pdf-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        padding: 12px;
        background: white;
        border: 1px solid #d0d7de;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .control-btn {
        width: 32px;
        height: 32px;
        border: 1px solid #d0d7de;
        background: #f6f8fa;
        color: #24292f;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .control-btn:hover:not(:disabled) {
        background: #f3f4f6;
        border-color: #8c959f;
    }

    .control-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f6f8fa;
        color: #8c959f;
    }

    .page-info {
        font-size: 14px;
        color: #24292f;
        font-weight: 500;
        padding: 0 16px;
        user-select: none;
    }

    .separator {
        color: #8c959f;
    }

    #current-page {
        color: #24292f;
    }

    #total-pages {
        color: #8c959f;
    }

    @media (max-width: 768px) {
        .header-content {
            flex-wrap: wrap;
            gap: 12px;
        }

        .pdf-content {
            padding: 24px 16px;
            margin: 16px 8px;
        }

        .pdf-viewer-container {
            padding: 0 16px;
        }

        .pdf-controls {
            margin: 0 8px;
        }
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const requestId = new URLSearchParams(window.location.search).get('request_id') || '';
    const pdfDebugConfig = {
        enabled: @json($pdfDebugEnabled ?? false),
        slowThreshold: @json($pdfDebugSlowThreshold ?? 4000),
        alertOnError: @json($pdfDebugAlertOnError ?? true),
        showTimeline: @json($pdfDebugShowTimeline ?? false),
    };

    function ensureRequestId(message = 'Request ID not found') {
        if (requestId) {
            return true;
        }
        alert(message);
        return false;
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderPdfDebugInfo(payload) {
        if (!pdfDebugConfig.enabled || !pdfDebugConfig.showTimeline) {
            return;
        }

        const panel = document.getElementById('pdf-debug-panel');
        const eventsList = document.getElementById('pdf-debug-events');
        const generatedAt = document.getElementById('pdf-debug-generated-at');
        if (!panel || !eventsList) {
            return;
        }

        panel.style.display = 'block';

        if (generatedAt && payload.generated_at) {
            try {
                const ts = new Date(payload.generated_at);
                generatedAt.textContent = `อัปเดตล่าสุด: ${ts.toLocaleString('th-TH', { hour12: false })}`;
            } catch (_) {
                generatedAt.textContent = payload.generated_at;
            }
        }

        const events = Array.isArray(payload.events) ? payload.events : [];
        if (!events.length) {
            eventsList.innerHTML = '<li class="pdf-debug-panel__empty">No debug events returned.</li>';
            return;
        }

        eventsList.innerHTML = events.map(evt => {
            const type = evt.type || '';
            const isError = type === 'error';
            const isSlow = type === 'end' && typeof evt.duration_ms === 'number' && pdfDebugConfig.slowThreshold && evt.duration_ms > pdfDebugConfig.slowThreshold;
            const classes = ['pdf-debug-event'];
            if (isError) classes.push('pdf-debug-event--error');
            if (isSlow) classes.push('pdf-debug-event--slow');
            const ctx = evt.context ? escapeHtml(JSON.stringify(evt.context)) : '';

            return `
                <li class="${classes.join(' ')}">
                    <div class="pdf-debug-event__header">
                        <span>${escapeHtml(evt.section || 'Unknown step')}</span>
                        <span>${escapeHtml(type.toUpperCase())}</span>
                    </div>
                    <div class="pdf-debug-event__meta">
                        เวลา: ${escapeHtml(evt.timestamp || '')} · ระยะเวลา: ${evt.duration_ms !== undefined ? escapeHtml(evt.duration_ms + ' ms') : '-'}
                    </div>
                    ${ctx ? `<div class="pdf-debug-event__meta">Context: ${ctx}</div>` : ''}
                    ${evt.message ? `<div class="pdf-debug-event__meta">รายละเอียด: ${escapeHtml(evt.message)}</div>` : ''}
                </li>
            `;
        }).join('');
    }

    function showDebugAlerts(alerts) {
        if (!alerts || !alerts.length || !pdfDebugConfig.alertOnError) return;
        const level = alerts.some(msg => msg.toLowerCase().includes('error') || msg.includes('ข้อผิดพลาด')) ? 'error' : 'warning';
        if (window.Swal) {
            Swal.fire({
                icon: level,
                title: level === 'error' ? 'PDF Debug Error' : 'PDF Debug Alert',
                text: alerts.join('\n'),
                confirmButtonColor: '#C19165'
            });
        } else {
            alert(alerts.join('\n'));
        }
    }

    function parsePdfDebugHeader(headerValue) {
        if (!headerValue) {
            return null;
        }

        try {
            const binary = atob(headerValue);
            let jsonString;
            if (window.TextDecoder) {
                const bytes = new Uint8Array(binary.length);
                for (let i = 0; i < binary.length; i++) {
                    bytes[i] = binary.charCodeAt(i);
                }
                jsonString = new TextDecoder('utf-8').decode(bytes);
            } else {
                jsonString = decodeURIComponent(escape(binary));
            }
            return JSON.parse(jsonString);
        } catch (error) {
            console.warn('Failed to decode PDF debug header', error);
            return null;
        }
    }

    function handlePdfDebugHeader(response) {
        if (!pdfDebugConfig.enabled || !response) {
            return null;
        }

        const header = response.headers.get('X-PDF-Debug');
        if (!header) {
            return null;
        }

        const payload = parsePdfDebugHeader(header);
        if (payload) {
            renderPdfDebugInfo(payload);
            if (payload.alerts && payload.alerts.length) {
                showDebugAlerts(payload.alerts);
            }
        }

        return payload;
    }

    function previewServiceAgreement() {
        if (!ensureRequestId()) return;
        window.location.href = `/print-service-agreement?request_id=${encodeURIComponent(requestId)}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const rentalAgreementBtn = document.getElementById('previewRentalAgreementPdfBtn');
        if (rentalAgreementBtn) {
            rentalAgreementBtn.addEventListener('click', (e) => {
                try { e.preventDefault(); e.stopPropagation(); } catch (_) {}
                if (!ensureRequestId()) return;
                window.location.href = `/print-agreement?request_id=${encodeURIComponent(requestId)}`;
            });
        }

        const canvas = document.getElementById('pdf-canvas');
        const ctx = canvas.getContext('2d');
        const loadingElement = document.getElementById('pdf-loading');
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        const downloadBtn = document.getElementById('downloadPdf');

        const tenantBtn = document.getElementById('tenantSignatureBtn');
        const witness1Btn = document.getElementById('witness1SignatureBtn');
        const witness2Btn = document.getElementById('witness2SignatureBtn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const tenantRoute = '{{ route('generate.tenant.signature.token') }}';
        const witness1RouteBase = '{{ url('/generate-witness1-token') }}';
        const witness2RouteBase = '{{ url('/generate-witness2-token') }}';

        if (!requestId) {
            loadingElement.innerHTML = '<p>No request ID found. Please go back and try again.</p>';
            [tenantBtn, witness1Btn, witness2Btn, prevBtn, nextBtn, downloadBtn].forEach(btn => btn && (btn.disabled = true));
            return;
        }

        let pdfDoc = null;
        let pageNum = 1;
        let pageRendering = false;
        let pageNumPending = null;
        const scale = 1.5;

        function updatePageIndicators() {
            document.getElementById('current-page').textContent = pageNum;
            document.getElementById('total-pages').textContent = pdfDoc?.numPages || 1;
        }

        function updateControls() {
            if (!pdfDoc) return;
            prevBtn.disabled = pageNum <= 1;
            nextBtn.disabled = pageNum >= pdfDoc.numPages;
        }

        function renderPage(num) {
            pageRendering = true;
            pdfDoc.getPage(num).then((page) => {
                const viewport = page.getViewport({ scale });
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                return page.render({ canvasContext: ctx, viewport }).promise;
            }).then(() => {
                pageRendering = false;
                if (pageNumPending !== null) {
                    renderPage(pageNumPending);
                    pageNumPending = null;
                }
                updatePageIndicators();
                updateControls();
            }).catch((error) => {
                console.error('Error rendering page:', error);
                loadingElement.innerHTML = '<p>Error rendering page. Please try again.</p>';
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

        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                prevBtn.click();
            } else if (e.key === 'ArrowRight') {
                nextBtn.click();
            }
        });

        const pdfFormData = new FormData();
        pdfFormData.append('request_id', requestId);

        fetch('{{ route("generate.print.reservation") }}', {
            method: 'POST',
            body: pdfFormData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/pdf'
            }
        })
            .then(async (response) => {
                handlePdfDebugHeader(response);
                const contentType = response.headers.get('content-type');
                if (!response.ok) {
                    if (contentType && contentType.includes('application/json')) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Error generating PDF');
                    }
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                if (!contentType || !contentType.includes('application/pdf')) {
                    throw new Error('Invalid response format: Expected PDF');
                }
                return response.arrayBuffer();
            })
            .then((data) => pdfjsLib.getDocument({ data }).promise)
            .then((pdf) => {
                pdfDoc = pdf;
                loadingElement.style.display = 'none';
                canvas.style.display = 'block';
                pageNum = 1;
                window.currentPdfData = pdf; // debugging only; not used elsewhere
                renderPage(pageNum);
            })
            .catch((error) => {
                console.error('Error generating PDF:', error);
                loadingElement.innerHTML = `<p>Error: ${error.message}</p>`;
            });

        downloadBtn.addEventListener('click', function () {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';

            const formData = new FormData();
            formData.append('request_id', requestId);

            fetch('{{ route("generate.print.reservation") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/pdf'
                }
            })
                .then((response) => {
                    handlePdfDebugHeader(response);
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.blob();
                })
                .then((blob) => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
                    a.download = `Reservation_Contract_${requestId}_${timestamp}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('Error downloading PDF. Please try again.');
                })
                .finally(() => {
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-download"></i> Download PDF';
                });
        });

        function legacyCopyText(text) {
            return new Promise((resolve, reject) => {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.setAttribute('readonly', '');
                textarea.setAttribute('tabindex', '-1');
                textarea.style.position = 'fixed';
                textarea.style.top = '-1000px';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);

                const selection = document.getSelection();
                const selectedRange = selection && selection.rangeCount > 0 ? selection.getRangeAt(0) : null;

                try {
                    textarea.focus({ preventScroll: true });
                    textarea.select();
                    textarea.selectionStart = 0;
                    textarea.selectionEnd = text.length;

                    const listener = (event) => {
                        event.clipboardData.setData('text/plain', text);
                        event.preventDefault();
                    };

                    document.addEventListener('copy', listener, { once: true });
                    const successful = document.execCommand('copy');
                    document.removeEventListener('copy', listener);

                    if (!successful) {
                        throw new Error('Copy command was unsuccessful');
                    }
                    resolve();
                } catch (err) {
                    reject(err);
                } finally {
                    document.body.removeChild(textarea);
                    if (selectedRange && selection) {
                        selection.removeAllRanges();
                        selection.addRange(selectedRange);
                    }
                }
            });
        }

        function copyTextToClipboard(text) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text).catch(() => legacyCopyText(text));
            }
            return legacyCopyText(text);
        }

        const roleLabel = {
            tenant: 'ผู้เช่า',
            witness1: 'พยานคนที่ 1',
            witness2: 'พยานคนที่ 2'
        };

        const roleUrlPrefix = {
            tenant: '/tenant-signature/',
            witness1: '/witness1-signature/',
            witness2: '/witness2-signature/'
        };

        const signatureButtons = {
            tenant: tenantBtn,
            witness1: witness1Btn,
            witness2: witness2Btn
        };

        const signatureTypes = ['tenant', 'witness1', 'witness2'];

        function setButtonLoading(btn, isLoading) {
            if (!btn) return;
            if (isLoading) {
                btn.disabled = true;
                btn.dataset.loading = 'true';
            } else {
                btn.disabled = false;
                delete btn.dataset.loading;
            }
        }

        function initializeSignatureButtons() {
            signatureTypes.forEach((type) => {
                const btn = signatureButtons[type];
                if (!btn) return;
                setButtonLoading(btn, true);
                btn.dataset.url = '';
            });
        }

        const endpointConfig = {
            tenant: () => ({
                url: tenantRoute,
                options: {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ request_id: requestId, type: 'rent' })
                }
            }),
            witness1: () => ({
                url: `${(witness1RouteBase || '').replace(/\/$/, '')}/${requestId}`,
                options: {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            }),
            witness2: () => ({
                url: `${(witness2RouteBase || '').replace(/\/$/, '')}/${requestId}`,
                options: {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            })
        };

        function fetchSignatureUrl(type) {
            const configFactory = endpointConfig[type];
            if (!configFactory) {
                return Promise.reject(new Error('ไม่พบปลายทางสำหรับการสร้างลิงก์'));
            }

            const { url, options } = configFactory();
            if (!url) {
                return Promise.reject(new Error('ไม่พบ URL สำหรับการสร้างลิงก์'));
            }

            return fetch(url, options)
                .then((r) => r.json())
                .then((data) => {
                    if (!data.success || !data.token) throw new Error(data.message || 'ไม่สามารถสร้างลิงก์ได้');
                    return `${window.location.origin}${roleUrlPrefix[type]}${data.token}`;
                });
        }

        function openLinkOrCopyFallback(url, type) {
            const newTab = window.open(url, '_blank', 'noopener');
            if (newTab && typeof newTab.focus === 'function') {
                newTab.focus();
                Swal?.fire({
                    icon: 'success',
                    title: 'เปิดหน้าลายเซ็นแล้ว',
                    text: `ระบบเปิดแท็บใหม่สำหรับ${roleLabel[type]}เรียบร้อย`,
                    timer: 2000,
                    showConfirmButton: false,
                });
                return Promise.resolve();
            }

            throw new Error('เบราว์เซอร์บล็อกหน้าต่างใหม่ กรุณาปลดบล็อก popup แล้วลองอีกครั้ง');
        }

        function ensureLinkAndCopy(type, btn) {
            if (btn.dataset.loading === 'true') {
                const waitingMessage = 'ระบบกำลังกำหนดลิงก์ โปรดรอสักครู่ก่อนกดอีกครั้ง';
                if (window.Swal) {
                    Swal.fire({
                        icon: 'info',
                        title: 'กำลังเตรียมลิงก์',
                        text: waitingMessage,
                        confirmButtonColor: '#C19165'
                    });
                } else {
                    alert(waitingMessage);
                }
                return Promise.resolve();
            }

            const existingUrl = btn.dataset.url;
            if (existingUrl) {
                return openLinkOrCopyFallback(existingUrl, type);
            }

            btn.disabled = true;
            btn.dataset.loading = 'true';

            return fetchSignatureUrl(type)
                .then((url) => {
                    btn.dataset.url = url;
                    return openLinkOrCopyFallback(url, type);
                })
                .finally(() => {
                    btn.disabled = false;
                    delete btn.dataset.loading;
                });
        }

        function handleSignatureButtonClick(type) {
            const btn = signatureButtons[type];
            if (!btn) return;

            btn.addEventListener('click', () => {
                ensureLinkAndCopy(type, btn).catch((err) => {
                    if (err && err.message) {
                        alert(err.message);
                    }
                });
            });
        }

        function preloadSignatureLinks() {
            signatureTypes.forEach((type) => {
                const btn = signatureButtons[type];
                if (!btn) return;
                setButtonLoading(btn, true);
                fetchSignatureUrl(type)
                    .then((url) => {
                        btn.dataset.url = url;
                        btn.dataset.preloadError = '';
                        setButtonLoading(btn, false);
                    })
                    .catch((err) => {
                        console.warn(`ไม่สามารถสร้างลิงก์ล่วงหน้าสำหรับ ${type}:`, err?.message || err);
                        btn.dataset.url = '';
                        btn.dataset.preloadError = 'true';
                        setButtonLoading(btn, false);
                    });
            });
        }

        initializeSignatureButtons();
        handleSignatureButtonClick('tenant');
        handleSignatureButtonClick('witness1');
        handleSignatureButtonClick('witness2');
        preloadSignatureLinks();
    });
</script>
@endsection