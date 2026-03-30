@extends('layouts.app')

@section('title', 'Buy/Sale Pipeline')

@section('styles')
<style>
    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .page-header h3 { font-weight: 700; margin: 0; }
    .btn-create {
        background: var(--primary);
        border: none;
        border-radius: var(--radius-sm);
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.6rem 1.4rem;
        color: #fff;
        box-shadow: var(--shadow-sm);
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-create:hover {
        background: var(--primary-dark);
        color: #fff;
        box-shadow: 0 4px 14px rgba(42,139,146,0.35);
        transform: translateY(-1px);
    }

    /* Status filter card */
    .filter-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.75rem;
        box-shadow: var(--shadow-sm);
    }
    .filter-card-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-dark);
        margin-bottom: 0.85rem;
    }
    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border-radius: 999px;
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
        font-weight: 500;
        border: 1px solid var(--border);
        color: var(--text-mid);
        background: var(--surface);
        transition: all 0.15s;
        text-decoration: none;
    }
    .status-chip:hover { background: var(--cream); color: var(--text-dark); border-color: var(--primary); }
    .status-chip.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
    }
    .status-chip i { font-size: 0.95rem; }

    /* Cards */
    .pipeline-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.35rem;
        transition: all 0.2s ease;
        position: relative;
        overflow: visible;
    }
    .pipeline-card:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--primary);
        transform: translateY(-2px);
    }
    .pipeline-card.highlight {
        animation: cardPulse 2s ease;
    }
    @keyframes cardPulse {
        0%, 100% { box-shadow: none; }
        30%, 70% { box-shadow: 0 0 0 3px var(--primary), 0 8px 24px rgba(42,139,146,0.15); }
    }

    /* Status pill */
    .status-pill {
        display: inline-block;
        border-radius: 999px;
        font-weight: 600;
        padding: 0.3rem 0.75rem;
        font-size: 0.78rem;
    }

    /* Three-dot menu */
    .card-menu-btn {
        background: none;
        border: none;
        color: var(--text-light);
        font-size: 1.15rem;
        padding: 0.1rem 0.35rem;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s;
        line-height: 1;
    }
    .card-menu-btn:hover { background: var(--cream); color: var(--text-dark); }
    .dropdown-menu {
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        padding: 0.35rem;
        min-width: 200px;
    }
    .dropdown-item {
        border-radius: var(--radius-sm);
        padding: 0.5rem 0.8rem;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-dark);
    }
    .dropdown-item:hover { background: var(--primary-muted); color: var(--primary-dark); }
    .col-xl-4:has(.dropdown-menu.show),
    .col-lg-6:has(.dropdown-menu.show) {
        z-index: 1050;
        position: relative;
    }
    .dropdown-submenu { position: relative; }
    .dropdown-submenu > .dropdown-menu {
        display: none;
        position: absolute;
        top: 0;
        left: auto;
        right: 100%;
        margin-right: 2px;
        z-index: 1060;
    }
    /* Desktop (non-touch): hover to open */
    @media (hover: hover) and (pointer: fine) {
        .dropdown-submenu:hover > .dropdown-menu { display: block; }
    }
    /* Touch / non-hover: click to toggle via .open class */
    .dropdown-submenu.open > .dropdown-menu { display: block; }
    .dropdown-menu > li:first-child > .dropdown-divider { display: none; }
    @media (max-width: 768px) {
        .dropdown-submenu > .dropdown-menu {
            position: static;
            box-shadow: none;
            border: none;
            padding-left: 1rem;
            margin: 0;
        }
    }

    /* Card content */
    .card-label {
        font-size: 0.75rem;
        color: var(--text-light);
        font-weight: 500;
        margin-bottom: 0.1rem;
    }
    .card-project-name {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.25rem;
    }
    .card-location {
        font-size: 0.82rem;
        color: var(--text-mid);
    }
    .card-location i { font-size: 0.8rem; }
    .card-price {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-dark);
    }
    .card-metrics {
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        margin-top: 0.75rem;
    }
    .card-metric-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-dark);
    }
    .card-customer {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .card-customer-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--cream);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-mid);
        font-size: 0.85rem;
    }
    .card-customer-name {
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    /* Card footer actions */
    .card-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid var(--border);
    }
    .card-actions .badge-completed {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--primary-dark);
        background: rgba(42,139,146,0.1);
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
    }

    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
        color: var(--text-light);
    }
    .empty-state p {
        color: #6b7280;
    }

    .pagination-shell {
        display: flex;
        flex-direction: column;
        gap: 20px;
        align-items: center;
        padding: 0.9rem 1.25rem;
        margin-top: 1.5rem;
    }
    .pagination-summary {
        font-size: 0.85rem;
        color: var(--text-mid);
        font-weight: 500;
    }
    .pagination-mini {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.85rem;
    }
    .pagination-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50px;
        border: 1px solid var(--border);
        color: var(--text-mid);
        font-weight: 600;
        transition: all 0.15s ease;
        text-decoration: none;
        background: #fff;
    }
    .pagination-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
    }
    .pagination-btn.active {
        border-color: var(--primary);
        background: var(--primary);
        color: #fff;
        box-shadow: 0 4px 12px rgba(42,139,146,0.25);
    }
    .pagination-btn.disabled,
    .pagination-btn.disabled:hover {
        border-color: var(--border);
        color: #c0c5cb;
        cursor: not-allowed;
        background: #f3f4f6;
        box-shadow: none;
    }
    .pagination-ellipsis {
        color: var(--text-light);
        font-weight: 600;
    }
    .modal .form-label { font-weight: 600; }
</style>
@endsection

@section('content')
    {{-- Header --}}
    <div class="page-header">
        <h3>Buy/Sale</h3>
        @permission('buy_sale.create')
        <button class="btn-create" data-bs-toggle="modal" data-bs-target="#createSaleModal">
            <i class="bi bi-plus me-1"></i> Create Appointment
        </button>
        @endpermission
    </div>

    {{-- Project + Unit Search --}}
    <div class="filter-card">
        <div class="filter-card-title">Search by Project / Unit</div>
        <form action="{{ route('buy-sale.index') }}" method="GET" class="row g-3 align-items-end" id="pipelineFilterForm">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-uppercase text-muted">Project</label>
                <select name="project" class="form-select" data-filter-select>
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ (string)$projectId === (string)$project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-uppercase text-muted">Unit Code</label>
                <input type="text" name="unit_code" class="form-control" placeholder="Search unit code" value="{{ $unitCode }}" data-filter-unit>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-uppercase text-muted">Unit Type</label>
                <select name="unit_type" class="form-select" data-filter-select>
                    <option value="">All Types</option>
                    @foreach($unitTypes as $type)
                        <option value="{{ $type }}" {{ $unitType === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-uppercase text-muted">Bedroom</label>
                <select name="bedrooms" class="form-select" data-filter-select>
                    <option value="">All Bedrooms</option>
                    @foreach($bedroomOptions as $bed)
                        <option value="{{ $bed }}" {{ (string)$bedrooms === (string)$bed ? 'selected' : '' }}>
                            {{ $bed }} Bedroom{{ $bed > 1 ? 's' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    {{-- Status Filter --}}
    <div class="filter-card">
        <div class="filter-card-title">Project Status</div>
        <div class="d-flex flex-wrap gap-2">
            @php $sequence = array_keys($statusFlow); @endphp
            @php $filterParams = array_filter(['project' => $projectId, 'unit_code' => $unitCode, 'unit_type' => $unitType, 'bedrooms' => $bedrooms]); @endphp
            <a href="{{ route('buy-sale.index', $filterParams) }}" class="status-chip {{ !$status ? 'active' : '' }}">
                <i class="bi bi-grid"></i> All
            </a>
            @foreach($sequence as $key)
                <a href="{{ route('buy-sale.index', array_merge($filterParams, ['status' => $key])) }}"
                   class="status-chip {{ $status === $key ? 'active' : '' }}">
                    <i class="bi {{ $statusFlow[$key]['icon'] }}"></i> {{ $statusFlow[$key]['label'] }}
                </a>
            @endforeach
        </div>
    </div>


    {{-- Cards --}}
    <div class="row g-4">
        @forelse($sales as $sale)
            @php
                $currentIndex = array_search($sale->status, $sequence, true);
                $nextStatus = ($currentIndex !== false && $currentIndex < count($sequence) - 1)
                    ? $sequence[$currentIndex + 1]
                    : null;
                $statusMeta = $statusFlow[$sale->status] ?? null;
                $listing = $sale->listing;
                $hasListing = $listing !== null;
                $buildingName = $hasListing ? ($listing->project->name ?? '-') : '-';
                $floorLabel = $hasListing && $listing->floor ? 'Floor ' . $listing->floor : null;
                $roomLabel = $hasListing && $listing->room_number ? 'Room ' . $listing->room_number : null;
                $unitCodeLabel = $hasListing && $listing->unit_code ? '(' . $listing->unit_code . ')' : null;
                $unitInfo = $hasListing ? trim(collect([$buildingName, $floorLabel, $roomLabel])->filter()->join(' • ') . ' ' . ($unitCodeLabel ?? '')) : '';
                $price = $hasListing ? $listing->price_per_room : null;
                $hasCustomer = !empty($sale->reservation_data);
                $customerName = $hasCustomer
                    ? ($sale->reservation_data['first_name'] ?? '') . ' ' . ($sale->reservation_data['last_name'] ?? '')
                    : null;

                $nextLabel = $nextStatus ? $statusFlow[$nextStatus]['label'] : null;
                $formStatuses = ['reserved' => 'Reservation', 'contract' => 'Contract'];
                $availableStatuses = ['available'];
            @endphp
            <div class="col-xl-4 col-lg-6" id="sale-{{ $sale->id }}">
                <div class="pipeline-card h-100 {{ request('highlight') == $sale->id ? 'highlight' : '' }}">
                    {{-- Top: Status + Menu --}}
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="status-pill"
                              style="color: {{ $statusMeta['color'] ?? '#101828' }}; background: {{ $statusMeta['bg'] ?? 'rgba(0,0,0,0.06)' }};">
                            {{ $statusMeta['label'] ?? ucfirst($sale->status) }}
                        </span>
                        <div class="dropdown">
                            <button class="card-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                {{-- 1. Make / Advance action --}}
                                @permission('buy_sale.advance')
                                @if($nextStatus && $sale->status !== 'transferred')
                                    @if(in_array($nextStatus, $availableStatuses))
                                        <li>
                                            <button type="button" class="dropdown-item advance-available-trigger"
                                                    data-sale-id="{{ $sale->id }}"
                                                    data-route="{{ route('buy-sale.advance', $sale) }}">
                                                <i class="bi bi-check-circle me-2" style="color:#12b76a;"></i>
                                                Advance to Available (Select Unit)
                                            </button>
                                        </li>
                                    @elseif(isset($formStatuses[$nextStatus]))
                                        <li>
                                            <a class="dropdown-item" href="{{ route('buy-sale.form', ['sale' => $sale->id, 'type' => $nextStatus]) }}">
                                                <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                                                Make {{ $formStatuses[$nextStatus] }}
                                            </a>
                                        </li>
                                    @else
                                        @php
                                            $blockAdvance = false;
                                            if ($sale->status === 'installment') {
                                                $inst = $sale->purchaseAgreement?->installments ?? collect();
                                                $blockAdvance = $inst->isEmpty() || $inst->contains(fn($i) => !$i->proof_image);
                                            }
                                        @endphp
                                        <li>
                                            @if($blockAdvance)
                                                <button type="button" class="dropdown-item text-muted" disabled title="ต้องชำระค่างวดให้ครบทุกงวดก่อน">
                                                    <i class="bi bi-lock me-2"></i>
                                                    Advance to {{ $nextLabel }}
                                                    <span class="d-block small text-danger" style="margin-left:1.5rem;">ผ่อนยังไม่ครบ</span>
                                                </button>
                                            @else
                                                <form action="{{ route('buy-sale.advance', $sale) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-arrow-right-circle me-2 text-success"></i>
                                                        Advance to {{ $nextLabel }}
                                                    </button>
                                                </form>
                                            @endif
                                        </li>
                                    @endif
                                @endif
                                @endpermission

                                {{-- 2. Print menu --}}
                                @php
                                    $printItems = [];
                                    if (in_array($sale->status, ['reserved', 'contract', 'installment', 'transferred'])) {
                                        $printItems[] = ['label' => 'Reservation Agreement', 'route' => route('contracts.reservation-agreement.preview-page', ['sale' => $sale->id])];
                                        $printItems[] = ['label' => 'Addendum', 'route' => route('contracts.addendum.preview-page', ['sale' => $sale->id])];
                                    }
                                    if (in_array($sale->status, ['contract', 'installment', 'transferred'])) {
                                        $printItems[] = ['label' => 'Sale and Purchase Agreement', 'route' => route('contracts.purchase-agreement.preview-page', ['sale' => $sale->id])];
                                    }
                                    if (in_array($sale->status, ['installment', 'transferred'])) {
                                        $printItems[] = ['label' => 'Deal Slip', 'route' => route('contracts.deal-slip.preview-page', ['sale' => $sale->id])];
                                    }
                                @endphp
                                @if(count($printItems))
                                    <li><hr class="dropdown-divider"></li>
                                    @if(count($printItems) === 1)
                                        <li>
                                            <a class="dropdown-item" href="{{ $printItems[0]['route'] }}">
                                                <i class="bi bi-printer me-2 text-info"></i>
                                                Print {{ $printItems[0]['label'] }}
                                            </a>
                                        </li>
                                    @else
                                        <li class="dropdown-submenu">
                                            <a class="dropdown-item" href="#">
                                                <i class="bi bi-printer me-2 text-info"></i>
                                                Print
                                                <i class="bi bi-chevron-right float-end" style="font-size:0.7rem; margin-top:3px;"></i>
                                            </a>
                                            <ul class="dropdown-menu">
                                                @foreach($printItems as $item)
                                                    <li>
                                                        <a class="dropdown-item" href="{{ $item['route'] }}">
                                                            <i class="bi bi-file-earmark-text me-2 text-info"></i>
                                                            {{ $item['label'] }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                    @endif
                                @endif

                                {{-- 3. View Installments --}}
                                @if(in_array($sale->status, ['installment', 'transferred'], true))
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('buy-sale.installments', $sale) }}">
                                            <i class="bi bi-credit-card me-2 text-primary"></i>
                                            View Installments
                                        </a>
                                    </li>
                                @endif

                                {{-- 4. Note / Remark --}}
                                @permission('buy_sale.remarks')
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button type="button"
                                            class="dropdown-item remark-trigger"
                                            data-route="{{ route('buy-sale.remarks', $sale) }}"
                                            data-status="{{ $sale->status }}"
                                            data-status-label="{{ $statusMeta['label'] ?? ucfirst($sale->status) }}"
                                            data-remark="{{ $sale->status === 'appointment' ? ($sale->appointment?->remark ?? '') : ($sale->{$remarkColumns[$sale->status] ?? ''} ?? '') }}">
                                        <i class="bi bi-journal-text me-2 text-secondary"></i>
                                        Note / Remark
                                    </button>
                                </li>
                                @endpermission

                                {{-- 5. Cancel --}}
                                @permission('buy_sale.cancel')
                                @if($sale->status !== 'appointment' && $sale->status !== 'transferred')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('buy-sale.cancel', $sale) }}" method="POST"
                                              onsubmit="return confirm('ยืนยันการยกเลิก? ข้อมูลในฟอร์มทั้งหมดจะถูกลบและสถานะจะกลับเป็น Appointment')">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-x-circle me-2"></i>
                                                Cancel Sale
                                            </button>
                                        </form>
                                    </li>
                                @endif
                                @endpermission
                            </ul>
                        </div>
                    </div>

                    {{-- Unit Details --}}
                    @if($hasListing)
                        <div class="card-label">ตึก / Tower</div>
                        <div class="card-project-name">{{ $unitInfo ?: $buildingName }}</div>
                        <div class="card-metrics">
                            <div>
                                <div class="card-label text-uppercase mb-1">พื้นที่ / Area</div>
                                <div class="card-metric-value">
                                    {{ $listing->area ? number_format($listing->area, 2) . ' sqm' : '-' }}
                                </div>
                            </div>
                            <div>
                                <div class="card-label text-uppercase mb-1">Unit Type</div>
                                <div class="card-metric-value">{{ $listing->unit_type ?? '-' }}</div>
                            </div>
                        </div>
                    @endif

                    {{-- Appointment Info --}}
                    @if($sale->appointment?->appointment_date)
                        <div class="mt-2">
                            <div class="card-label text-uppercase mb-1">วันนัดหมาย</div>
                            <div class="card-metric-value" style="color:#7c3aed;">
                                <i class="bi bi-calendar-check me-1"></i>
                                {{ $sale->appointment->appointment_date->format('d M Y') }}
                                @if($sale->appointment->appointment_time)
                                    {{ \Carbon\Carbon::createFromFormat('H:i:s', $sale->appointment->appointment_time)->format('H:i') }}
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($sale->appointment?->remark && $sale->status === 'appointment')
                        <div class="mt-2">
                            <div class="card-label text-uppercase mb-1">หมายเหตุ</div>
                            <div class="small" style="color:#667085; white-space:pre-line;">{{ $sale->appointment->remark }}</div>
                        </div>
                    @endif

                    @if($sale->user && $sale->status === 'appointment')
                        <div class="mt-2">
                            <div class="card-label text-uppercase mb-1">เซลผู้ดูแล</div>
                            <div class="d-flex align-items-center gap-2">
                                @if($sale->user->avatar)
                                    <img src="{{ asset('storage/' . $sale->user->avatar) }}" alt=""
                                         class="rounded-circle" style="width:24px;height:24px;object-fit:cover;">
                                @else
                                    <span class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                                          style="width:24px;height:24px;background:#d0d5dd;color:#475467;font-size:0.65rem;">
                                        {{ strtoupper(Str::limit($sale->user->name, 2, '')) }}
                                    </span>
                                @endif
                                <span class="small fw-medium" style="color:#475467;">{{ $sale->user->name }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Price + Contract Info --}}
                    <div class="mt-3 d-flex flex-wrap gap-4 align-items-end">
                        @if($hasListing)
                            <div>
                                <div class="card-label">Price</div>
                                <div class="card-price">{{ $price ? '฿' . number_format($price, 0) : '-' }}</div>
                            </div>
                        @endif

                        @if(in_array($sale->status, ['contract', 'installment'], true))
                            @php
                                $agreement = $sale->purchaseAgreement;
                                $totalTerm = $agreement?->total_term;
                                $installmentSum = (float) ($sale->purchase_agreement_installments_sum_amount_number ?? 0);
                            @endphp
                            <div class="card-metrics mt-0">
                                <div>
                                    <div class="card-label text-uppercase mb-1">จำนวนงวด</div>
                                    <div class="card-metric-value">{{ $totalTerm !== null ? $totalTerm . ' งวด' : '-' }}</div>
                                </div>
                                <div>
                                    <div class="card-label text-uppercase mb-1">ยอดรวมผ่อนชำระ</div>
                                    <div class="card-metric-value">{{ $installmentSum > 0 ? '฿' . number_format($installmentSum, 2) : '-' }}</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Created By --}}
                    @if($sale->user)
                        <div class="mt-3 d-none">
                            <div class="card-label">Created By</div>
                            <div class="card-customer mt-1">
                                <div class="card-customer-avatar">
                                    @if($sale->user->avatar)
                                        <img src="{{ asset('storage/' . $sale->user->avatar) }}" alt="{{ $sale->user->name }}"
                                             class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                                    @else
                                        <span class="rounded-circle d-flex align-items-center justify-content-center fw-semibold" style="width:32px;height:32px;background:#d0d5dd;color:#475467;font-size:0.8rem;">
                                            {{ strtoupper(Str::limit($sale->user->name, 2, '')) }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <div class="card-customer-name">
                                        {{ $sale->user->name }} ({{ ucfirst($sale->user->role ?? 'agent') }})
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php
                        $currentRemark = $sale->status === 'appointment'
                            ? ($sale->appointment?->remark ?? null)
                            : ($sale->{$remarkColumns[$sale->status] ?? null} ?? null);

                        $appointmentRemarkText = $sale->appointment?->remark;
                        $remarkEntries = collect(array_merge(
                            [['status' => 'appointment', 'label' => $statusFlow['appointment']['label'] ?? 'Appointment', 'remark' => $appointmentRemarkText, 'hasRemark' => filled($appointmentRemarkText)]],
                            collect($remarkColumns)
                                ->map(function ($column, $statusKey) use ($sale, $statusFlow) {
                                    $remarkText = $sale->{$column};
                                    return [
                                        'status' => $statusKey,
                                        'label' => $statusFlow[$statusKey]['label'] ?? ucfirst($statusKey),
                                        'remark' => $remarkText,
                                        'hasRemark' => filled($remarkText),
                                    ];
                                })
                                ->values()
                                ->all()
                        ))->all();
                        $remarkUnitLabel = $hasListing && $listing->unit_code
                            ? 'Unit ' . $listing->unit_code
                            : ($unitInfo ?: 'Sale #' . $sale->id);
                    @endphp

                    {{-- Footer --}}
                    <div class="card-actions d-flex flex-wrap gap-2 align-items-center">
                        @if($sale->status === 'transferred')
                            <span class="badge-completed">
                                <i class="bi bi-check-circle-fill"></i> Transferred
                            </span>
                        @elseif($sale->status === 'available' && $hasListing)
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary btn-quotation-visitor"
                                    style="border-radius:8px; font-size:0.8rem;"
                                    data-sale-id="{{ $sale->id }}"
                                    data-listing-id="{{ $sale->listing_id }}"
                                    data-language="th"
                                    data-avail-name="{{ $sale->avail_name }}"
                                    data-avail-tel="{{ $sale->avail_tel }}">
                                <i class="bi bi-file-earmark-text me-1"></i>Quotation (TH)
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary btn-quotation-visitor"
                                    style="border-radius:8px; font-size:0.8rem;"
                                    data-sale-id="{{ $sale->id }}"
                                    data-listing-id="{{ $sale->listing_id }}"
                                    data-language="en"
                                    data-avail-name="{{ $sale->avail_name }}"
                                    data-avail-tel="{{ $sale->avail_tel }}">
                                <i class="bi bi-file-earmark-text me-1"></i>Quotation (EN)
                            </button>
                        @endif

                        <button type="button"
                                class="btn btn-sm btn-outline-secondary remark-history-trigger ms-auto"
                                style="border-radius:8px; font-size:0.8rem;"
                                title="View remarks"
                                data-remarks='@json($remarkEntries)'
                                data-unit="{{ $remarkUnitLabel }}">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="pipeline-card empty-state">
                    <i class="bi bi-inboxes fs-1 mb-3"></i>
                    <p class="mb-0">No sales found for this status.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($sales->total() > 0)
        @php
            $firstItem = $sales->firstItem();
            $lastItem = $sales->lastItem();
            $totalItems = $sales->total();
            $currentPage = $sales->currentPage();
            $lastPage = $sales->lastPage();
        @endphp

        <div class="pagination-shell">
            <div class="pagination-summary">
                Showing {{ $firstItem }} to {{ $lastItem }} of {{ number_format($totalItems) }} results
            </div>
            @if($sales->hasPages())
                <div class="pagination-mini">
                    @php
                        $prevUrl = $sales->previousPageUrl();
                        $nextUrl = $sales->nextPageUrl();
                    @endphp
                    <a class="pagination-btn {{ $sales->onFirstPage() ? 'disabled' : '' }}" href="{{ $sales->onFirstPage() ? '#' : $prevUrl }}" aria-label="Previous page">
                        &lt;
                    </a>

                    <a class="pagination-btn {{ $currentPage === 1 ? 'active' : '' }}" href="{{ $sales->url(1) }}">1</a>

                    @if($currentPage > 2)
                        <span class="pagination-ellipsis">&hellip;</span>
                    @endif

                    @if($currentPage > 1 && $currentPage < $lastPage)
                        <a class="pagination-btn active" href="{{ $sales->url($currentPage) }}">{{ $currentPage }}</a>
                    @endif

                    @if($currentPage < $lastPage - 1)
                        <span class="pagination-ellipsis">&hellip;</span>
                    @endif

                    @if($lastPage > 1)
                        <a class="pagination-btn {{ $currentPage === $lastPage ? 'active' : '' }}" href="{{ $sales->url($lastPage) }}">{{ $lastPage }}</a>
                    @endif

                    <a class="pagination-btn {{ $currentPage === $lastPage ? 'disabled' : '' }}" href="{{ $currentPage === $lastPage ? '#' : $nextUrl }}" aria-label="Next page">
                        &gt;
                    </a>
                </div>
            @endif
        </div>
    @endif

    {{-- Create Appointment Modal --}}
    <div class="modal fade" id="createSaleModal" tabindex="-1" aria-labelledby="createSaleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('buy-sale.store') }}" method="POST" id="createAppointmentForm">
                    @csrf
                    <input type="hidden" name="appointment_time" id="createAppointmentTimeInput">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createSaleModalLabel">
                            <i class="bi bi-calendar-check me-2" style="color:#7c3aed;"></i>Create Appointment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">วันที่นัดหมาย <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="appointment_date" id="createAppointmentDate" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">เวลานัดหมาย <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 align-items-center">
                                <select class="form-select" id="createAppointmentHour" style="max-width:110px;">
                                    @for($h = 0; $h <= 23; $h++)
                                        <option value="{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}" {{ $h === 9 ? 'selected' : '' }}>
                                            {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00 น.
                                        </option>
                                    @endfor
                                </select>
                                <span class="text-muted fw-semibold">:</span>
                                <select class="form-select" id="createAppointmentMinute" style="max-width:100px;">
                                    @for($m = 0; $m <= 59; $m++)
                                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                                            {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">หมายเหตุ</label>
                            <textarea class="form-control" name="remark_appointment" rows="3" placeholder="ชื่อลูกค้า, เบอร์โทร, รายละเอียดเพิ่มเติม..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-check-lg me-1"></i> Create Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Advance to Available Modal (Select Listing) --}}
    <div class="modal fade" id="advanceAvailableModal" tabindex="-1" aria-labelledby="advanceAvailableModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="advanceAvailableForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="advanceAvailableModalLabel">
                            <i class="bi bi-check-circle me-2" style="color:#12b76a;"></i>Advance to Available — Select Unit
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="adv_project_id" class="form-label">Building (Project)</label>
                            <select id="adv_project_id" class="form-select" required>
                                <option value="">-- Select Building --</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="adv_floor_select" class="form-label">Floor</label>
                            <select id="adv_floor_select" class="form-select" disabled required>
                                <option value="">-- Select Floor --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="adv_listing_id" class="form-label">Room</label>
                            <select id="adv_listing_id" name="listing_id" class="form-select" disabled required>
                                <option value="">-- Select Room --</option>
                            </select>
                        </div>
                        <div id="advUnitPreview" class="card bg-light d-none">
                            <div class="card-body py-2">
                                <h6 class="card-title mb-2">Unit Details</h6>
                                <div class="row small">
                                    <div class="col-6 mb-1"><strong>Room:</strong> <span id="advPrevRoom">-</span></div>
                                    <div class="col-6 mb-1"><strong>Unit Code:</strong> <span id="advPrevCode">-</span></div>
                                    <div class="col-6 mb-1"><strong>Type:</strong> <span id="advPrevType">-</span></div>
                                    <div class="col-6 mb-1"><strong>Area:</strong> <span id="advPrevArea">-</span> sqm</div>
                                    <div class="col-6 mb-1"><strong>Price:</strong> <span id="advPrevPrice">-</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark" id="btnAdvanceAvailable" disabled>Advance to Available</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Remark Modal --}}
    <div class="modal fade" id="remarkModal" tabindex="-1" aria-labelledby="remarkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="remarkForm">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="remarkModalLabel">Note / Remark</h5>
                            <div class="text-muted small" id="remarkStatusLabel"></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="status" id="remarkStatusInput">
                        <input type="hidden" name="current_filter_status" value="{{ request('status') }}">
                        <input type="hidden" name="current_filter_project" value="{{ request('project') }}">
                        <input type="hidden" name="current_filter_unit" value="{{ request('unit_code') }}">
                        <label class="form-label fw-semibold">รายละเอียด</label>
                        <textarea class="form-control" name="remark" id="remarkTextarea" rows="4" placeholder="Add notes about this stage..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark">Save Remark</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Remark History Modal --}}
    <div class="modal fade" id="remarkHistoryModal" tabindex="-1" aria-labelledby="remarkHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="remarkHistoryModalLabel">Remark History</h5>
                        <div class="text-muted small" id="remarkHistoryUnit"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="remarkHistoryEmpty" class="text-center text-muted py-4" style="display:none;">
                        <i class="bi bi-journal-text fs-2 mb-2 d-block"></i>
                        No remarks recorded yet.
                    </div>
                    <div id="remarkHistoryShell">
                        <div id="remarkHistoryTabs" class="nav nav-pills flex-nowrap gap-2 mb-3 overflow-auto"></div>
                        <div id="remarkHistoryContent" class="border rounded-3 p-3" style="min-height: 150px; background:#f8fafc;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Quotation Visitor Modal --}}
    <div class="modal fade" id="quotationVisitorModal" tabindex="-1" aria-labelledby="quotationVisitorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quotationVisitorModalLabel">Visitor Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="qvSaleId">
                    <input type="hidden" id="qvListingId">
                    <input type="hidden" id="qvLanguage">
                    <div class="mb-3">
                        <label for="qvVisitorName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="qvVisitorName" placeholder="Enter visitor name">
                        <div class="invalid-feedback" id="qvVisitorNameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="qvVisitorPhone" class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="qvVisitorPhone" placeholder="Enter phone number">
                        <div class="invalid-feedback" id="qvVisitorPhoneError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="qvSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="qvSpinner"></span>
                        Save & Preview
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Dropdown submenu: click toggle for touch devices ──
    document.querySelectorAll('.dropdown-submenu > .dropdown-item').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            // On touch devices, toggle submenu; on desktop hover handles it
            if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                e.preventDefault();
                e.stopPropagation();
                const parent = this.closest('.dropdown-submenu');
                // Close other open submenus
                document.querySelectorAll('.dropdown-submenu.open').forEach(function (el) {
                    if (el !== parent) el.classList.remove('open');
                });
                parent.classList.toggle('open');
            }
        });
    });
    // Close submenu when parent dropdown closes
    document.addEventListener('hide.bs.dropdown', function () {
        document.querySelectorAll('.dropdown-submenu.open').forEach(function (el) {
            el.classList.remove('open');
        });
    });

    const floorsEndpointTemplate = "{{ route('buy-sale.api.floors', ['project' => '__PROJECT__'], false) }}";
    const unitsEndpointTemplate = "{{ route('buy-sale.api.units', ['project' => '__PROJECT__', 'floor' => '__FLOOR__'], false) }}";

    // ── Create Appointment form submit ──
    const createForm = document.getElementById('createAppointmentForm');
    const createTimeInput = document.getElementById('createAppointmentTimeInput');
    const createHour = document.getElementById('createAppointmentHour');
    const createMinute = document.getElementById('createAppointmentMinute');

    if (createForm && createTimeInput && createHour && createMinute) {
        createForm.addEventListener('submit', function (e) {
            e.preventDefault();
            createTimeInput.value = createHour.value + ':' + createMinute.value + ':00';
            createForm.submit();
        });
    }

    // ── Advance to Available modal (Building/Floor/Room picker) ──
    function setupListingPicker(projectId, floorId, listingId, previewId, btnId, prefixId) {
        const projectSelect = document.getElementById(projectId);
        const floorSelect = document.getElementById(floorId);
        const listingSelect = document.getElementById(listingId);
        const unitPreview = document.getElementById(previewId);
        const btnSubmit = document.getElementById(btnId);

        if (!projectSelect || !floorSelect || !listingSelect) return;

        let unitsData = [];

        projectSelect.addEventListener('change', function () {
            floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';
            floorSelect.disabled = true;
            listingSelect.innerHTML = '<option value="">-- Select Room --</option>';
            listingSelect.disabled = true;
            if (unitPreview) unitPreview.classList.add('d-none');
            if (btnSubmit) btnSubmit.disabled = true;
            if (!this.value) return;

            fetch(floorsEndpointTemplate.replace('__PROJECT__', encodeURIComponent(this.value)))
                .then(r => r.json())
                .then(floors => {
                    floors.forEach(f => {
                        const opt = document.createElement('option');
                        opt.value = f;
                        opt.textContent = 'Floor ' + f;
                        floorSelect.appendChild(opt);
                    });
                    floorSelect.disabled = false;
                })
                .catch(() => { floorSelect.disabled = true; });
        });

        floorSelect.addEventListener('change', function () {
            listingSelect.innerHTML = '<option value="">-- Select Room --</option>';
            listingSelect.disabled = true;
            if (unitPreview) unitPreview.classList.add('d-none');
            if (btnSubmit) btnSubmit.disabled = true;
            unitsData = [];
            if (!this.value) return;

            fetch(unitsEndpointTemplate
                .replace('__PROJECT__', encodeURIComponent(projectSelect.value))
                .replace('__FLOOR__', encodeURIComponent(this.value)))
                .then(r => r.json())
                .then(units => {
                    unitsData = units;
                    units.forEach(u => {
                        const opt = document.createElement('option');
                        opt.value = u.id;
                        opt.textContent = u.room_number + (u.unit_code ? ' - ' + u.unit_code : '');
                        listingSelect.appendChild(opt);
                    });
                    listingSelect.disabled = false;
                })
                .catch(() => { listingSelect.disabled = true; });
        });

        listingSelect.addEventListener('change', function () {
            if (unitPreview) unitPreview.classList.add('d-none');
            if (btnSubmit) btnSubmit.disabled = true;
            if (!this.value) return;
            const unit = unitsData.find(u => u.id == this.value);
            if (unit && unitPreview) {
                document.getElementById(prefixId + 'Room').textContent = unit.room_number || '-';
                document.getElementById(prefixId + 'Code').textContent = unit.unit_code || '-';
                document.getElementById(prefixId + 'Type').textContent = unit.unit_type || '-';
                document.getElementById(prefixId + 'Area').textContent = unit.area ? Number(unit.area).toLocaleString('en', {minimumFractionDigits: 2}) : '-';
                document.getElementById(prefixId + 'Price').textContent = unit.price_per_room ? Number(unit.price_per_room).toLocaleString('en', {minimumFractionDigits: 2}) : '-';
                unitPreview.classList.remove('d-none');
            }
            if (btnSubmit) btnSubmit.disabled = false;
        });

        return { projectSelect, floorSelect, listingSelect, unitPreview, btnSubmit, reset() {
            projectSelect.value = '';
            floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';
            floorSelect.disabled = true;
            listingSelect.innerHTML = '<option value="">-- Select Room --</option>';
            listingSelect.disabled = true;
            if (unitPreview) unitPreview.classList.add('d-none');
            if (btnSubmit) btnSubmit.disabled = true;
            unitsData = [];
        }};
    }

    // Setup advance-to-available picker
    const advPicker = setupListingPicker('adv_project_id', 'adv_floor_select', 'adv_listing_id', 'advUnitPreview', 'btnAdvanceAvailable', 'advPrev');

    const advModalEl = document.getElementById('advanceAvailableModal');
    const advForm = document.getElementById('advanceAvailableForm');

    if (advModalEl && advForm && window.bootstrap) {
        const advModal = new bootstrap.Modal(advModalEl);

        document.querySelectorAll('.advance-available-trigger').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                advForm.action = trigger.dataset.route;
                if (advPicker) advPicker.reset();
                advModal.show();
            });
        });

        advModalEl.addEventListener('hidden.bs.modal', () => {
            if (advPicker) advPicker.reset();
        });
    }

    // Scroll to highlighted card
    const highlight = document.querySelector('.pipeline-card.highlight');
    if (highlight) {
        setTimeout(() => {
            highlight.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 300);
    }

    // Auto-submit filters when selects change or unit code typing pauses
    const filterForm = document.getElementById('pipelineFilterForm');
    if (filterForm) {
        const selectFilters = filterForm.querySelectorAll('[data-filter-select]');
        const unitFilter = filterForm.querySelector('[data-filter-unit]');
        const submitFilters = () => {
            if (filterForm.requestSubmit) {
                filterForm.requestSubmit();
            } else {
                filterForm.submit();
            }
        };

        selectFilters.forEach(sel => {
            sel.addEventListener('change', submitFilters);
        });

        if (unitFilter) {
            let debounceId = null;
            const scheduleSubmit = () => {
                clearTimeout(debounceId);
                debounceId = setTimeout(submitFilters, 500);
            };
            unitFilter.addEventListener('input', scheduleSubmit);
            unitFilter.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    submitFilters();
                }
            });
            unitFilter.addEventListener('blur', () => {
                if (unitFilter.value.trim() !== '') {
                    submitFilters();
                }
            });
        }
    }

    // Remark modal triggers
    const remarkModalEl = document.getElementById('remarkModal');
    const remarkForm = document.getElementById('remarkForm');
    const remarkTextarea = document.getElementById('remarkTextarea');
    const remarkStatusInput = document.getElementById('remarkStatusInput');
    const remarkStatusLabel = document.getElementById('remarkStatusLabel');

    if (remarkModalEl && remarkForm && remarkTextarea && remarkStatusInput && remarkStatusLabel && window.bootstrap) {
        const remarkModal = new bootstrap.Modal(remarkModalEl);
        document.querySelectorAll('.remark-trigger').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const { route, status, remark, statusLabel } = trigger.dataset;
                remarkForm.action = route;
                remarkStatusInput.value = status || '';
                remarkStatusLabel.textContent = statusLabel || '';
                remarkTextarea.value = remark || '';
                remarkModal.show();
            });
        });

        remarkModalEl.addEventListener('hidden.bs.modal', () => {
            remarkForm.reset();
            remarkStatusLabel.textContent = '';
        });
    }

    const remarkHistoryModalEl = document.getElementById('remarkHistoryModal');
    const remarkHistoryShell = document.getElementById('remarkHistoryShell');
    const remarkHistoryTabs = document.getElementById('remarkHistoryTabs');
    const remarkHistoryContent = document.getElementById('remarkHistoryContent');
    const remarkHistoryEmpty = document.getElementById('remarkHistoryEmpty');
    const remarkHistoryUnit = document.getElementById('remarkHistoryUnit');

    if (
        remarkHistoryModalEl &&
        remarkHistoryShell &&
        remarkHistoryTabs &&
        remarkHistoryContent &&
        remarkHistoryEmpty &&
        remarkHistoryUnit &&
        window.bootstrap
    ) {
        const historyModal = new bootstrap.Modal(remarkHistoryModalEl);

        const renderContent = (entry) => {
            remarkHistoryContent.innerHTML = '';
            if (!entry) {
                const emptyState = document.createElement('div');
                emptyState.className = 'text-muted fst-italic';
                emptyState.textContent = 'No remark data available.';
                remarkHistoryContent.appendChild(emptyState);
                return;
            }

            const header = document.createElement('div');
            header.className = 'small text-uppercase text-muted mb-2';
            header.textContent = entry.label;

            const body = document.createElement('div');
            if (entry.hasRemark) {
                body.className = 'text-secondary';
                body.style.whiteSpace = 'pre-line';
                body.textContent = entry.remark;
            } else {
                body.className = 'text-muted fst-italic';
                body.textContent = 'No remark recorded for this status yet.';
            }

            remarkHistoryContent.appendChild(header);
            remarkHistoryContent.appendChild(body);
        };

        const activateTab = (buttons, index, entries) => {
            buttons.forEach((btn, idx) => {
                if (idx === index) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            renderContent(entries[index]);
        };

        document.querySelectorAll('.remark-history-trigger').forEach((trigger) => {
             trigger.addEventListener('click', () => {
                 const entries = JSON.parse(trigger.dataset.remarks || '[]');
                 const unit = trigger.dataset.unit || '';
                 remarkHistoryUnit.textContent = unit;
                 remarkHistoryTabs.innerHTML = '';
                 remarkHistoryContent.innerHTML = '';

                 if (!entries.length) {
                     remarkHistoryShell.style.display = 'none';
                     remarkHistoryEmpty.style.display = '';
                     historyModal.show();
                     return;
                 }

                 remarkHistoryShell.style.display = '';
                 remarkHistoryEmpty.style.display = 'none';

                 const tabButtons = [];
                 entries.forEach((entry, index) => {
                     const tab = document.createElement('button');
                     tab.type = 'button';
                     tab.className = 'nav-link text-nowrap';
                     tab.textContent = entry.label;
                     if (!entry.hasRemark) {
                         tab.classList.add('opacity-50');
                     }
                     tab.addEventListener('click', () => activateTab(tabButtons, index, entries));
                     remarkHistoryTabs.appendChild(tab);
                     tabButtons.push(tab);
                 });

                 activateTab(tabButtons, 0, entries);
                 historyModal.show();
             });
        });

        remarkHistoryModalEl.addEventListener('hidden.bs.modal', () => {
            remarkHistoryTabs.innerHTML = '';
            remarkHistoryContent.innerHTML = '';
            remarkHistoryShell.style.display = '';
            remarkHistoryEmpty.style.display = 'none';
            remarkHistoryUnit.textContent = '';
        });
    }
});

// ── Quotation Visitor Modal ──
(function () {
    const modalEl = document.getElementById('quotationVisitorModal');
    if (!modalEl) return;

    const modal = new bootstrap.Modal(modalEl);
    const saleIdInput = document.getElementById('qvSaleId');
    const listingIdInput = document.getElementById('qvListingId');
    const languageInput = document.getElementById('qvLanguage');
    const nameInput = document.getElementById('qvVisitorName');
    const phoneInput = document.getElementById('qvVisitorPhone');
    const nameError = document.getElementById('qvVisitorNameError');
    const phoneError = document.getElementById('qvVisitorPhoneError');
    const submitBtn = document.getElementById('qvSubmitBtn');
    const spinner = document.getElementById('qvSpinner');

    document.querySelectorAll('.btn-quotation-visitor').forEach(btn => {
        btn.addEventListener('click', () => {
            saleIdInput.value = btn.dataset.saleId;
            listingIdInput.value = btn.dataset.listingId;
            languageInput.value = btn.dataset.language;
            nameInput.value = btn.dataset.availName || '';
            phoneInput.value = btn.dataset.availTel || '';
            nameInput.classList.remove('is-invalid');
            phoneInput.classList.remove('is-invalid');
            modal.show();
        });
    });

    submitBtn.addEventListener('click', () => {
        let hasError = false;
        nameInput.classList.remove('is-invalid');
        phoneInput.classList.remove('is-invalid');

        if (!nameInput.value.trim()) {
            nameInput.classList.add('is-invalid');
            nameError.textContent = 'Please enter visitor name.';
            hasError = true;
        }
        if (!phoneInput.value.trim()) {
            phoneInput.classList.add('is-invalid');
            phoneError.textContent = 'Please enter phone number.';
            hasError = true;
        }
        if (hasError) return;

        submitBtn.disabled = true;
        spinner.classList.remove('d-none');

        fetch(`/buy-sale/${saleIdInput.value}/quotation-visitor`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                visitor_name: nameInput.value.trim(),
                visitor_phone: phoneInput.value.trim(),
                language: languageInput.value,
            }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update the data attributes so next open shows new values
                const btns = document.querySelectorAll(`.btn-quotation-visitor[data-sale-id="${saleIdInput.value}"]`);
                btns.forEach(b => {
                    b.dataset.availName = nameInput.value.trim();
                    b.dataset.availTel = phoneInput.value.trim();
                });
                window.open(data.redirect_url, '_blank');
                modal.hide();
            } else {
                alert('Failed to save visitor information.');
            }
        })
        .catch(() => alert('An error occurred. Please try again.'))
        .finally(() => {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        });
    });
})();
</script>
@endsection
