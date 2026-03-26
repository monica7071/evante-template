@extends('layouts.app')

@section('title', 'Installment Tracking')

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

    .summary-card {
        background: #fff;
        border: 1px solid #e4e7ec;
        border-radius: 16px;
        padding: 1.35rem 1.5rem;
        margin-bottom: 1.5rem;
    }
    .summary-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
    }
    .summary-item .label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #98a2b3;
        text-transform: uppercase;
        margin-bottom: 0.2rem;
    }
    .summary-item .value {
        font-size: 1rem;
        font-weight: 700;
        color: #101828;
    }

    .installment-row {
        background: #fff;
        border: 1.5px solid #e4e7ec;
        border-radius: 14px;
        padding: 1.1rem 1.35rem;
        margin-bottom: 0.75rem;
        transition: box-shadow 0.15s;
    }
    .installment-row:hover { box-shadow: 0 2px 12px rgba(15,23,42,0.07); }

    .installment-row.status-paid {
        border-color: #12b76a;
        background: #f6fef9;
    }
    .installment-row.status-overdue {
        border-color: #f04438;
        background: #fffbfa;
    }
    .installment-row.status-upcoming {
        border-color: #e4e7ec;
    }

    .installment-seq {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        flex-shrink: 0;
    }
    .status-paid .installment-seq   { background: rgba(18,183,106,0.12); color: #12b76a; }
    .status-overdue .installment-seq { background: rgba(240,68,56,0.12); color: #f04438; }
    .status-upcoming .installment-seq { background: #f2f4f7; color: #667085; }

    .installment-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.65rem;
        border-radius: 8px;
    }
    .badge-paid     { background: rgba(18,183,106,0.12); color: #12b76a; }
    .badge-overdue  { background: rgba(240,68,56,0.12); color: #f04438; }
    .badge-upcoming { background: #f2f4f7; color: #667085; }

    .proof-thumb {
        width: 52px;
        height: 52px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e4e7ec;
        cursor: pointer;
    }

    .btn-upload {
        font-size: 0.82rem;
        font-weight: 600;
        border-radius: 8px;
        padding: 0.4rem 0.9rem;
    }

    .upload-form { display: none; }
    .upload-form.show { display: block; }
</style>
@endsection

@section('content')
    {{-- Header --}}
    <div class="page-header">
        <a href="{{ route('buy-sale.index', ['status' => 'installment']) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3>Installment Tracking</h3>
    </div>

    {{-- Summary --}}
    @php
        $listing  = $sale->listing;
        $project  = $listing->project;
        $buyer    = $agreement?->buyer_full_name ?? ($sale->reservation_data['first_name'] ?? '') . ' ' . ($sale->reservation_data['last_name'] ?? '');
        $unitInfo = collect([$project->name ?? null, $listing->floor ? 'Floor '.$listing->floor : null, $listing->room_number ? 'Room '.$listing->room_number : null])->filter()->join(' • ');
        $paidCount      = $installments->filter(fn($i) => $i->proof_image)->count();
        $overdueCount   = $installments->filter(fn($i) => !$i->proof_image && $i->due_date && $i->due_date->lt($today))->count();
        $totalTerm      = $agreement?->total_term ?? $installments->count();
        $totalAmount    = $installments->sum(fn($i) => (float) $i->amount_number);
        $paidAmount     = $installments->filter(fn($i) => $i->proof_image)->sum(fn($i) => (float) $i->amount_number);
    @endphp

    <div class="summary-card">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Unit</div>
                <div class="value">{{ $listing->unit_code ?? '-' }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Buyer</div>
                <div class="value">{{ trim($buyer) ?: '-' }}</div>
            </div>
            <div class="summary-item">
                <div class="label">ผ่อนไปแล้ว / ทั้งหมด</div>
                <div class="value">
                    <span style="color:#12b76a;">฿{{ number_format($paidAmount, 0) }}</span>
                    <span class="text-muted fw-normal" style="font-size:0.85rem;"> / ฿{{ number_format($totalAmount, 0) }}</span>
                </div>
            </div>
            <div class="summary-item">
                <div class="label">งวดทั้งหมด</div>
                <div class="value">{{ $totalTerm }} งวด</div>
            </div>
            <div class="summary-item">
                <div class="label">ชำระแล้ว</div>
                <div class="value" style="color:#12b76a;">{{ $paidCount }} งวด</div>
            </div>
            @if($overdueCount > 0)
                <div class="summary-item">
                    <div class="label">เลยกำหนด</div>
                    <div class="value" style="color:#f04438;">{{ $overdueCount }} งวด</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Installment Rows --}}
    @if($installments->isEmpty())
        <div class="summary-card text-center text-muted py-5">
            <i class="bi bi-credit-card fs-1 mb-3 d-block"></i>
            No installment data found.
        </div>
    @else
        @foreach($installments as $installment)
            @php
                $isPaid    = (bool) $installment->proof_image;
                $isOverdue = !$isPaid && $installment->due_date && $installment->due_date->lt($today);
                $rowClass  = $isPaid ? 'status-paid' : ($isOverdue ? 'status-overdue' : 'status-upcoming');
            @endphp
            <div class="installment-row {{ $rowClass }}">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    {{-- Sequence --}}
                    <div class="installment-seq">{{ $installment->sequence }}</div>

                    {{-- Info --}}
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <span class="fw-700" style="font-size:0.95rem;font-weight:700;">
                                ฿{{ number_format($installment->amount_number, 0) }}
                            </span>
                            @if($installment->amount_text)
                                <span class="text-muted small">({{ $installment->amount_text }})</span>
                            @endif

                            @if($isPaid)
                                <span class="installment-badge badge-paid"><i class="bi bi-check-circle-fill"></i> ชำระแล้ว</span>
                            @elseif($isOverdue)
                                <span class="installment-badge badge-overdue"><i class="bi bi-exclamation-circle-fill"></i> เลยกำหนด</span>
                            @else
                                <span class="installment-badge badge-upcoming"><i class="bi bi-clock"></i> รอชำระ</span>
                            @endif
                        </div>
                        @if($installment->due_date)
                            <div class="small" style="color: {{ $isOverdue ? '#f04438' : '#667085' }};">
                                <i class="bi bi-calendar3 me-1"></i>
                                กำหนดชำระ: {{ $installment->due_date->format('d M Y') }}
                                @if($isOverdue)
                                    <span class="ms-1">(เลยกำหนด {{ $today->diffInDays($installment->due_date) }} วัน)</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Proof section --}}
                    <div class="d-flex align-items-center gap-2">
                        @if($installment->proof_image)
                            <a href="{{ asset('storage/'.$installment->proof_image) }}" target="_blank">
                                <img src="{{ asset('storage/'.$installment->proof_image) }}"
                                     class="proof-thumb" alt="Payment proof" title="Click to view full image">
                            </a>
                        @endif

                        <button type="button"
                                class="btn btn-sm btn-upload {{ $isPaid ? 'btn-outline-success' : ($isOverdue ? 'btn-outline-danger' : 'btn-outline-secondary') }}"
                                onclick="toggleUpload({{ $installment->id }})">
                            <i class="bi bi-upload me-1"></i>
                            {{ $isPaid ? 'เปลี่ยนหลักฐาน' : 'อัปโหลดหลักฐาน' }}
                        </button>
                    </div>
                </div>

                {{-- Upload form (hidden by default) --}}
                <div class="upload-form mt-3" id="upload-{{ $installment->id }}">
                    <form action="{{ route('buy-sale.installments.proof', ['sale' => $sale->id, 'installment' => $installment->id]) }}"
                          method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <input type="file" name="proof_image" class="form-control form-control-sm"
                                   accept="image/*" style="max-width:280px;" required>
                            <button type="submit" class="btn btn-sm btn-dark btn-upload">
                                <i class="bi bi-check-lg me-1"></i> บันทึก
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-upload"
                                    onclick="toggleUpload({{ $installment->id }})">
                                ยกเลิก
                            </button>
                        </div>
                        <div class="form-text mt-1">รองรับ JPG, PNG ขนาดไม่เกิน 5MB</div>
                    </form>
                </div>
            </div>
        @endforeach
    @endif

    {{-- Documents Section --}}
    @php
        $totalInstallments = $installments->count();
        $secondToLast = $totalInstallments >= 2 ? $totalInstallments - 1 : null;
        $canPrintDealSlip = $secondToLast && $installments->firstWhere('sequence', $secondToLast)?->proof_image;
        $hasOverdue = $installments->contains(fn($i) => !$i->proof_image && $i->due_date && $i->due_date->lt($today));
    @endphp

    <div class="summary-card mt-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-text me-2"></i>Documents</h5>

        @php
            $dealSlipApproval = $sale->dealSlipApproval;
            $isApproved = $dealSlipApproval?->status === 'approved';
        @endphp
        <div class="d-flex flex-wrap gap-2">
            @if($canPrintDealSlip)
                @if($isApproved)
                    <a href="{{ route('contracts.deal-slip.preview-page', ['sale' => $sale->id]) }}" class="btn btn-success">
                        <i class="bi bi-printer me-1"></i> Print Deal Slip
                    </a>
                @else
                    <a href="{{ route('buy-sale.deal-slip', $sale) }}" class="btn btn-warning text-dark">
                        <i class="bi bi-hourglass-split me-1"></i> Deal Slip (รอดำเนินการ)
                    </a>
                @endif
            @else
                <button disabled class="btn btn-secondary" title="ชำระงวดรองสุดท้ายก่อน">
                    <i class="bi bi-printer me-1"></i> Deal Slip (ยังไม่พร้อม)
                </button>
            @endif

            @if($hasOverdue)
                <a href="{{ route('contracts.overdue-reminder-1.preview-page', ['sale' => $sale->id]) }}" class="btn btn-outline-danger">
                    <i class="bi bi-exclamation-triangle me-1"></i> Print Overdue Reminder (1st Notice)
                </a>
                <a href="{{ route('contracts.overdue-reminder-2.preview-page', ['sale' => $sale->id]) }}" class="btn btn-outline-danger">
                    <i class="bi bi-exclamation-triangle me-1"></i> Print Overdue Reminder (2nd Notice)
                </a>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
<script>
function toggleUpload(id) {
    const el = document.getElementById('upload-' + id);
    el.classList.toggle('show');
}
</script>
@endsection
