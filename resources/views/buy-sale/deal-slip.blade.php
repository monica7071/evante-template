@extends('layouts.app')

@section('title', 'Deal Slip Approval')

@section('styles')
<style>
    body { background: #f5f5f7; }

    .page-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .page-header h3 { font-weight: 700; margin: 0; }

    /* Stepper */
    .stepper {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        gap: 0;
        margin-bottom: 1rem;
    }
    .stepper-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        flex: 1;
        max-width: 220px;
    }
    .stepper-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 700;
        background: #f2f4f7;
        color: #98a2b3;
        border: 3px solid #e4e7ec;
        position: relative;
        z-index: 2;
        transition: all 0.3s ease;
    }
    .stepper-step.done .stepper-circle {
        background: #12b76a;
        color: #fff;
        border-color: #12b76a;
    }
    .stepper-step.active .stepper-circle {
        background: #fff;
        color: var(--primary, #7c3aed);
        border-color: var(--primary, #7c3aed);
        box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.15);
    }
    .stepper-label {
        margin-top: 0.6rem;
        font-size: 0.82rem;
        font-weight: 700;
        color: #98a2b3;
        text-align: center;
    }
    .stepper-step.done .stepper-label,
    .stepper-step.active .stepper-label { color: #101828; }
    .stepper-info {
        font-size: 0.72rem;
        color: #667085;
        text-align: center;
        margin-top: 0.2rem;
        min-height: 2.5rem;
    }
    .stepper-line {
        flex: 1;
        height: 3px;
        background: #e4e7ec;
        margin-top: 24px;
        max-width: 80px;
        transition: background 0.3s ease;
    }
    .stepper-line.done { background: #12b76a; }

    /* Cards */
    .approval-card {
        background: #fff;
        border: 1px solid #e4e7ec;
        border-radius: 16px;
        padding: 1.5rem;
    }

    /* Signature */
    .signature-canvas-wrap {
        border: 2px dashed #d0d5dd;
        border-radius: 12px;
        background: #fafafa;
        position: relative;
    }
    .signature-canvas-wrap canvas {
        width: 100%;
        height: 180px;
        display: block;
        border-radius: 10px;
    }
    .signature-canvas-wrap.drawing {
        border-color: var(--primary, #7c3aed);
        background: #fff;
    }

    /* Completed signature display */
    .sig-display {
        border: 1px solid #e4e7ec;
        border-radius: 10px;
        padding: 0.75rem;
        background: #f9fafb;
        text-align: center;
    }
    .sig-display img {
        max-height: 80px;
        max-width: 100%;
    }

    /* Preview area */
    .preview-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 300px;
        color: #98a2b3;
        gap: 1rem;
    }
    .preview-placeholder i { font-size: 3rem; }

    .btn-action {
        border-radius: 10px;
        padding: 0.55rem 1.5rem;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .sign-section { margin-top: 1.5rem; }
    .sign-section h6 { font-weight: 700; font-size: 0.9rem; }
</style>
@endsection

@section('content')
    @php
        $status = $approval?->status;
        $stepIndex = match($status) {
            'prepare' => 1,
            'check' => 2,
            'approved' => 3,
            default => 0,
        };
        $isSalesManager = auth()->user()->isSalesManager() || auth()->user()->isSuperAdmin() || auth()->user()->isAdmin();
        $listing = $sale->listing;
        $project = $listing->project ?? null;
    @endphp

    {{-- Header --}}
    <div class="page-header">
        <a href="{{ route('buy-sale.installments', $sale) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3>Deal Slip Approval</h3>
        <span class="text-muted ms-2" style="font-size:0.85rem;">
            {{ $listing->unit_code ?? '' }} &bull; {{ $project->name ?? '' }}
        </span>
    </div>

    {{-- Stepper --}}
    <div class="approval-card mb-4">
        <div class="stepper" id="stepper">
            {{-- Step 1: Prepare --}}
            <div class="stepper-step {{ $stepIndex >= 1 ? ($stepIndex > 1 ? 'done' : 'active') : '' }}" id="step-prepare">
                <div class="stepper-circle">
                    @if($stepIndex > 1) <i class="bi bi-check-lg"></i> @else 1 @endif
                </div>
                <div class="stepper-label">Prepare</div>
                <div class="stepper-info" id="info-prepare">
                    @if($approval?->prepared_name)
                        {{ $approval->prepared_name }}<br>
                        {{ $approval->prepared_at?->format('d/m/Y H:i') }}
                    @endif
                </div>
            </div>
            <div class="stepper-line {{ $stepIndex >= 2 ? 'done' : '' }}" id="line-1"></div>

            {{-- Step 2: Check --}}
            <div class="stepper-step {{ $stepIndex >= 2 ? ($stepIndex > 2 ? 'done' : 'active') : '' }}" id="step-check">
                <div class="stepper-circle">
                    @if($stepIndex > 2) <i class="bi bi-check-lg"></i> @else 2 @endif
                </div>
                <div class="stepper-label">Check</div>
                <div class="stepper-info" id="info-check">
                    @if($approval?->checked_name)
                        {{ $approval->checked_name }}<br>
                        {{ $approval->checked_at?->format('d/m/Y H:i') }}
                    @endif
                </div>
            </div>
            <div class="stepper-line {{ $stepIndex >= 3 ? 'done' : '' }}" id="line-2"></div>

            {{-- Step 3: Approved --}}
            <div class="stepper-step {{ $stepIndex >= 3 ? 'done' : '' }}" id="step-approved">
                <div class="stepper-circle">
                    @if($stepIndex >= 3) <i class="bi bi-check-lg"></i> @else 3 @endif
                </div>
                <div class="stepper-label">Approved</div>
                <div class="stepper-info" id="info-approved">
                    @if($approval?->approved_name)
                        {{ $approval->approved_name }}<br>
                        {{ $approval->approved_at?->format('d/m/Y H:i') }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Completed Signatures Display --}}
        @if($approval)
        <div class="row g-3 mt-2" id="signatures-display">
            {{-- Prepare signature --}}
            <div class="col-md-4" id="sig-done-prepare" style="{{ $approval->prepared_signature ? '' : 'display:none' }}">
                <div class="sig-display">
                    <div class="small text-muted mb-1">Prepare</div>
                    @if($approval->prepared_signature)
                        <img src="{{ $approval->prepared_signature }}" alt="Prepare signature">
                        <div class="small fw-bold mt-1">{{ $approval->prepared_name }}</div>
                    @endif
                </div>
            </div>
            {{-- Check signature --}}
            <div class="col-md-4" id="sig-done-check" style="{{ $approval->checked_signature ? '' : 'display:none' }}">
                <div class="sig-display">
                    <div class="small text-muted mb-1">Check</div>
                    @if($approval->checked_signature)
                        <img src="{{ $approval->checked_signature }}" alt="Check signature">
                        <div class="small fw-bold mt-1">{{ $approval->checked_name }}</div>
                    @endif
                </div>
            </div>
            {{-- Approved signature --}}
            <div class="col-md-4" id="sig-done-approved" style="{{ $approval->approved_signature ? '' : 'display:none' }}">
                <div class="sig-display">
                    <div class="small text-muted mb-1">Approved</div>
                    @if($approval->approved_signature)
                        <img src="{{ $approval->approved_signature }}" alt="Approved signature">
                        <div class="small fw-bold mt-1">{{ $approval->approved_name }}</div>
                    @endif
                </div>
            </div>
        </div>
        @else
        <div class="row g-3 mt-2" id="signatures-display"></div>
        @endif
    </div>

    {{-- Sign & Action Section --}}
    <div class="approval-card mb-4" id="sign-action-section">
        @if($stepIndex < 3)
            @php
                $nextAction = match($stepIndex) {
                    0 => 'prepare',
                    1 => 'check',
                    2 => 'approve',
                    default => null,
                };
                $nextLabel = match($stepIndex) {
                    0 => 'เริ่มดำเนินการ (Prepare)',
                    1 => 'ส่งตรวจสอบ (Check)',
                    2 => 'อนุมัติ (Approve)',
                    default => '',
                };
                $nextBtnClass = match($stepIndex) {
                    0 => 'btn-dark',
                    1 => 'btn-primary',
                    2 => 'btn-success',
                    default => 'btn-dark',
                };
                $canAct = $stepIndex !== 2 || $isSalesManager;
            @endphp

            @if($canAct)
                <h6><i class="bi bi-pen me-2"></i>{{ $nextLabel }}</h6>
                <p class="text-muted small mb-3">กรุณาลงชื่อและเซ็นลายเซ็นเพื่อดำเนินการขั้นตอนนี้</p>

                {{-- Name input --}}
                <div class="mb-3">
                    <label class="form-label fw-bold small">ชื่อผู้ลงนาม</label>
                    <input type="text" id="signer-name" class="form-control" value="" placeholder="ชื่อ-นามสกุล" required style="max-width:400px;">
                </div>

                {{-- Signature pad --}}
                <label class="form-label fw-bold small">ลายเซ็น</label>
                <div class="signature-canvas-wrap" id="sig-wrap">
                    <canvas id="signature-canvas"></canvas>
                </div>

                <div class="d-flex gap-2 justify-content-between align-items-center mt-3">
                    <button class="btn btn-outline-secondary btn-sm" onclick="clearSignature()" style="border-radius:8px;">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> ล้างลายเซ็น
                    </button>
                    <button class="btn {{ $nextBtnClass }} btn-action" id="submit-btn" onclick="submitAction('{{ $nextAction }}')">
                        <i class="bi bi-check-lg me-1"></i> {{ $nextLabel }}
                    </button>
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-hourglass-split fs-3 d-block mb-2"></i>
                    <div class="fw-bold">รอการอนุมัติจาก Sales Manager</div>
                    <div class="small mt-1">ขั้นตอนนี้ต้องให้ Sales Manager เป็นผู้เซ็นอนุมัติ</div>
                </div>
            @endif
        @else
            <div class="text-center py-3">
                <span class="text-success fw-bold fs-5">
                    <i class="bi bi-check-circle-fill me-2"></i>อนุมัติเรียบร้อยแล้ว
                </span>
            </div>
        @endif
    </div>

    {{-- Preview Area --}}
    <div class="approval-card">
        <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-pdf me-2"></i>Deal Slip Preview</h6>
        <div id="preview-area">
            @if($status === 'approved')
                <iframe src="{{ route('contracts.deal-slip.preview', ['sale' => $sale->id]) }}"
                        style="width:100%; height:600px; border:1px solid #e4e7ec; border-radius:12px;">
                </iframe>
                <div class="text-center mt-3">
                    <a href="{{ route('contracts.deal-slip.download', ['sale' => $sale->id]) }}"
                       class="btn btn-dark btn-action">
                        <i class="bi bi-download me-1"></i> Download PDF
                    </a>
                </div>
            @else
                <div class="preview-placeholder">
                    <i class="bi bi-file-earmark-lock2"></i>
                    <div class="text-center">
                        <div class="fw-bold" style="color:#667085;">เอกสารจะแสดงเมื่อได้รับการอนุมัติ</div>
                        <div class="small mt-1">กรุณาดำเนินการตามขั้นตอนด้านบนให้ครบทุกขั้น</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
const actionUrl = @json(route('buy-sale.deal-slip.action', $sale));
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const previewUrl = @json(route('contracts.deal-slip.preview', ['sale' => $sale->id]));
const downloadUrl = @json(route('contracts.deal-slip.download', ['sale' => $sale->id]));
const isSalesManager = @json($isSalesManager);

let signaturePad = null;

// Init signature pad on load
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('signature-canvas');
    if (canvas) {
        resizeCanvas(canvas);
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255,255,255,0)',
            penColor: '#000',
        });
        const wrap = document.getElementById('sig-wrap');
        canvas.addEventListener('pointerdown', () => wrap.classList.add('drawing'));
        canvas.addEventListener('pointerup', () => wrap.classList.remove('drawing'));
    }
});

function resizeCanvas(canvas) {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext('2d').scale(ratio, ratio);
}

function clearSignature() {
    if (signaturePad) signaturePad.clear();
}

function submitAction(action) {
    const nameInput = document.getElementById('signer-name');
    const name = nameInput ? nameInput.value.trim() : '';

    if (!name) {
        Swal.fire({ icon: 'warning', title: 'กรุณากรอกชื่อผู้ลงนาม' });
        return;
    }
    if (!signaturePad || signaturePad.isEmpty()) {
        Swal.fire({ icon: 'warning', title: 'กรุณาเซ็นลายเซ็น' });
        return;
    }

    const signatureData = signaturePad.toDataURL('image/png');
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> กำลังบันทึก...';

    fetch(actionUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            action: action,
            signer_name: name,
            signature_data: signatureData,
        }),
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (ok && data.success) {
            updateUI(data.approval);
            Swal.fire({ icon: 'success', title: 'สำเร็จ', timer: 1500, showConfirmButton: false });
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> ลองอีกครั้ง';
            Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: data.message || 'กรุณาลองอีกครั้ง' });
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> ลองอีกครั้ง';
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'กรุณาลองอีกครั้ง' });
    });
}

function updateUI(approval) {
    const stepIndex = { prepare: 1, check: 2, approved: 3 }[approval.status] || 0;
    const steps = ['prepare', 'check', 'approved'];

    // Update stepper circles & labels
    steps.forEach((step, i) => {
        const el = document.getElementById('step-' + step);
        const circle = el.querySelector('.stepper-circle');
        el.classList.remove('done', 'active');
        if (i < stepIndex) {
            el.classList.add('done');
            circle.innerHTML = '<i class="bi bi-check-lg"></i>';
        } else if (i === stepIndex) {
            el.classList.add('active');
            circle.innerHTML = (i + 1);
        } else {
            circle.innerHTML = (i + 1);
        }
    });

    document.getElementById('line-1').classList.toggle('done', stepIndex >= 2);
    document.getElementById('line-2').classList.toggle('done', stepIndex >= 3);

    // Update step info text
    const fieldMap = {
        prepare: { name: 'prepared_name', at: 'prepared_at' },
        check:   { name: 'checked_name',  at: 'checked_at' },
        approved:{ name: 'approved_name',  at: 'approved_at' },
    };
    steps.forEach(step => {
        const f = fieldMap[step];
        const infoEl = document.getElementById('info-' + step);
        if (approval[f.name]) {
            const at = approval[f.at] ? new Date(approval[f.at]).toLocaleString('th-TH') : '';
            infoEl.innerHTML = approval[f.name] + '<br>' + at;
        }
    });

    // Update completed signatures display
    const sigMap = {
        prepare:  { sig: 'prepared_signature',  name: 'prepared_name' },
        check:    { sig: 'checked_signature',   name: 'checked_name' },
        approved: { sig: 'approved_signature',  name: 'approved_name' },
    };
    const sigDisplay = document.getElementById('signatures-display');
    steps.forEach(step => {
        const s = sigMap[step];
        let col = document.getElementById('sig-done-' + step);
        if (approval[s.sig]) {
            if (!col) {
                col = document.createElement('div');
                col.className = 'col-md-4';
                col.id = 'sig-done-' + step;
                sigDisplay.appendChild(col);
            }
            col.style.display = '';
            col.innerHTML = '<div class="sig-display">' +
                '<div class="small text-muted mb-1">' + step.charAt(0).toUpperCase() + step.slice(1) + '</div>' +
                '<img src="' + approval[s.sig] + '" alt="' + step + ' signature" style="max-height:80px;max-width:100%;">' +
                '<div class="small fw-bold mt-1">' + (approval[s.name] || '') + '</div>' +
                '</div>';
        }
    });

    // Update sign/action section
    const section = document.getElementById('sign-action-section');
    if (stepIndex >= 3) {
        // All done
        section.innerHTML = '<div class="text-center py-3"><span class="text-success fw-bold fs-5"><i class="bi bi-check-circle-fill me-2"></i>อนุมัติเรียบร้อยแล้ว</span></div>';
        showPdfPreview();
    } else {
        const nextAction = ['prepare', 'check', 'approve'][stepIndex];
        const nextLabel = ['เริ่มดำเนินการ (Prepare)', 'ส่งตรวจสอบ (Check)', 'อนุมัติ (Approve)'][stepIndex];
        const nextBtnClass = ['btn-dark', 'btn-primary', 'btn-success'][stepIndex];
        const canAct = stepIndex !== 2 || isSalesManager;

        if (canAct) {
            section.innerHTML =
                '<h6><i class="bi bi-pen me-2"></i>' + nextLabel + '</h6>' +
                '<p class="text-muted small mb-3">กรุณาลงชื่อและเซ็นลายเซ็นเพื่อดำเนินการขั้นตอนนี้</p>' +
                '<div class="mb-3">' +
                    '<label class="form-label fw-bold small">ชื่อผู้ลงนาม</label>' +
                    '<input type="text" id="signer-name" class="form-control" value="" placeholder="ชื่อ-นามสกุล" required style="max-width:400px;">' +
                '</div>' +
                '<label class="form-label fw-bold small">ลายเซ็น</label>' +
                '<div class="signature-canvas-wrap" id="sig-wrap">' +
                    '<canvas id="signature-canvas"></canvas>' +
                '</div>' +
                '<div class="d-flex gap-2 justify-content-between align-items-center mt-3">' +
                    '<button class="btn btn-outline-secondary btn-sm" onclick="clearSignature()" style="border-radius:8px;">' +
                        '<i class="bi bi-arrow-counterclockwise me-1"></i> ล้างลายเซ็น</button>' +
                    '<button class="btn ' + nextBtnClass + ' btn-action" id="submit-btn" onclick="submitAction(\'' + nextAction + '\')">' +
                        '<i class="bi bi-check-lg me-1"></i> ' + nextLabel + '</button>' +
                '</div>';

            // Re-init signature pad
            setTimeout(() => {
                const canvas = document.getElementById('signature-canvas');
                if (canvas) {
                    resizeCanvas(canvas);
                    signaturePad = new SignaturePad(canvas, {
                        backgroundColor: 'rgba(255,255,255,0)',
                        penColor: '#000',
                    });
                    const wrap = document.getElementById('sig-wrap');
                    canvas.addEventListener('pointerdown', () => wrap.classList.add('drawing'));
                    canvas.addEventListener('pointerup', () => wrap.classList.remove('drawing'));
                }
            }, 50);
        } else {
            section.innerHTML =
                '<div class="text-center py-4 text-muted">' +
                    '<i class="bi bi-hourglass-split fs-3 d-block mb-2"></i>' +
                    '<div class="fw-bold">รอการอนุมัติจาก Sales Manager</div>' +
                    '<div class="small mt-1">ขั้นตอนนี้ต้องให้ Sales Manager เป็นผู้เซ็นอนุมัติ</div>' +
                '</div>';
        }
    }
}

function showPdfPreview() {
    document.getElementById('preview-area').innerHTML =
        '<iframe src="' + previewUrl + '" style="width:100%;height:600px;border:1px solid #e4e7ec;border-radius:12px;"></iframe>' +
        '<div class="text-center mt-3"><a href="' + downloadUrl + '" class="btn btn-dark btn-action"><i class="bi bi-download me-1"></i> Download PDF</a></div>';
}
</script>
@endsection
