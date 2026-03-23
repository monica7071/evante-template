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
        left: 100%;
        margin-left: 2px;
        z-index: 1060;
    }
    .dropdown-submenu:hover > .dropdown-menu { display: block; }
    .dropdown-menu > li:first-child > .dropdown-divider { display: none; }

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
        <button class="btn-create" data-bs-toggle="modal" data-bs-target="#createSaleModal">
            <i class="bi bi-plus me-1"></i> Create Sale
        </button>
    </div>

    {{-- Project + Unit Search --}}
    <div class="filter-card">
        <div class="filter-card-title">Search by Project / Unit</div>
        <form action="{{ route('buy-sale.index') }}" method="GET" class="row g-3 align-items-end" id="pipelineFilterForm">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="col-md-4">
                <label class="form-label fw-semibold small text-uppercase text-muted">Project</label>
                <select name="project" class="form-select" data-filter-project>
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ (string)$projectId === (string)$project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small text-uppercase text-muted">Unit Code</label>
                <input type="text" name="unit_code" class="form-control" placeholder="Search unit code" value="{{ $unitCode }}" data-filter-unit>
            </div>
        </form>
    </div>

    {{-- Status Filter --}}
    <div class="filter-card">
        <div class="filter-card-title">Project Status</div>
        <div class="d-flex flex-wrap gap-2">
            @php $sequence = array_keys($statusFlow); @endphp
            <a href="{{ route('buy-sale.index', array_filter(['unit_code' => request('unit_code'), 'project' => $projectId])) }}" class="status-chip {{ !$status ? 'active' : '' }}">
                <i class="bi bi-grid"></i> All
            </a>
            @foreach($sequence as $key)
                <a href="{{ route('buy-sale.index', array_filter(['status' => $key, 'project' => $projectId, 'unit_code' => $unitCode])) }}"
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
                $buildingName = $listing->project->name ?? '-';
                $floorLabel = $listing->floor ? 'Floor ' . $listing->floor : null;
                $roomLabel = $listing->room_number ? 'Room ' . $listing->room_number : null;
                $unitCodeLabel = $listing->unit_code ? '(' . $listing->unit_code . ')' : null;
                $unitInfo = trim(collect([$buildingName, $floorLabel, $roomLabel])->filter()->join(' • ') . ' ' . ($unitCodeLabel ?? ''));
                $price = $listing->price_per_room;
                $hasCustomer = !empty($sale->reservation_data);
                $customerName = $hasCustomer
                    ? ($sale->reservation_data['first_name'] ?? '') . ' ' . ($sale->reservation_data['last_name'] ?? '')
                    : null;

                $nextLabel = $nextStatus ? $statusFlow[$nextStatus]['label'] : null;
                $formStatuses = ['reserved' => 'Reservation', 'contract' => 'Contract'];
                $appointmentStatuses = ['appointment'];
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
                                @if($nextStatus && $sale->status !== 'transferred')
                                    @if(in_array($nextStatus, $appointmentStatuses))
                                        <li>
                                            <button type="button" class="dropdown-item appointment-trigger"
                                                    data-sale-id="{{ $sale->id }}"
                                                    data-route="{{ route('buy-sale.advance', $sale) }}"
                                                    data-date="{{ $sale->appointment_date?->format('Y-m-d') ?? '' }}"
                                                    data-time="{{ $sale->appointment_time ?? '' }}">
                                                <i class="bi bi-calendar-check me-2" style="color:#7c3aed;"></i>
                                                Make Appointment
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
                                        <li>
                                            <form action="{{ route('buy-sale.advance', $sale) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-arrow-right-circle me-2 text-success"></i>
                                                    Advance to {{ $nextLabel }}
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                @endif

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
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button type="button"
                                            class="dropdown-item remark-trigger"
                                            data-route="{{ route('buy-sale.remarks', $sale) }}"
                                            data-status="{{ $sale->status }}"
                                            data-status-label="{{ $statusMeta['label'] ?? ucfirst($sale->status) }}"
                                            data-remark="{{ $sale->{$remarkColumns[$sale->status]} ?? '' }}">
                                        <i class="bi bi-journal-text me-2 text-secondary"></i>
                                        Note / Remark
                                    </button>
                                </li>

                                {{-- 5. Cancel --}}
                                @if($sale->status !== 'available' && $sale->status !== 'transferred')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('buy-sale.cancel', $sale) }}" method="POST"
                                              onsubmit="return confirm('ยืนยันการยกเลิก? ข้อมูลในฟอร์มทั้งหมดจะถูกลบและสถานะจะกลับเป็น Available')">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-x-circle me-2"></i>
                                                Cancel Sale
                                            </button>
                                        </form>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    {{-- Unit Details --}}
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

                    {{-- Price + Contract Info --}}
                    <div class="mt-3 d-flex flex-wrap gap-4 align-items-end">
                        <div>
                            <div class="card-label">Price</div>
                            <div class="card-price">{{ $price ? '฿' . number_format($price, 0) : '-' }}</div>
                        </div>

                        @if($sale->status === 'appointment' && $sale->appointment_date)
                            <div>
                                <div class="card-label text-uppercase mb-1">วันนัดหมาย</div>
                                <div class="card-metric-value" style="color:#7c3aed;">
                                    <i class="bi bi-calendar-check me-1"></i>
                                    {{ $sale->appointment_date->format('d M Y') }}
                                    @if($sale->appointment_time)
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $sale->appointment_time)->format('H:i') }}
                                    @endif
                                </div>
                                @if($sale->appointment_name)
                                    <div class="small mt-1" style="color:#667085;">
                                        <i class="bi bi-person me-1"></i>{{ $sale->appointment_name }}
                                        @if($sale->appointment_phone)
                                            &nbsp;·&nbsp;<i class="bi bi-telephone me-1"></i>{{ $sale->appointment_phone }}
                                        @endif
                                    </div>
                                @endif
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
                        $currentRemark = $sale->{$remarkColumns[$sale->status]} ?? null;
                        $remarkEntries = collect($remarkColumns)
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
                            ->all();
                        $remarkUnitLabel = $listing->unit_code
                            ? 'Unit ' . $listing->unit_code
                            : ($unitInfo ?: 'Sale #' . $sale->id);
                    @endphp

                    {{-- Footer --}}
                    <div class="card-actions d-flex flex-wrap gap-2 align-items-center">
                        @if($sale->status === 'transferred')
                            <span class="badge-completed">
                                <i class="bi bi-check-circle-fill"></i> Transferred
                            </span>
                        @elseif($sale->status === 'available')
                            <a href="{{ route('contracts.quotation.preview-listing', ['listing' => $sale->listing_id, 'language' => 'th']) }}"
                               class="btn btn-sm btn-outline-secondary" style="border-radius:8px; font-size:0.8rem;">
                                <i class="bi bi-file-earmark-text me-1"></i>Quotation (TH)
                            </a>
                            <a href="{{ route('contracts.quotation.preview-listing', ['listing' => $sale->listing_id, 'language' => 'en']) }}"
                               class="btn btn-sm btn-outline-secondary" style="border-radius:8px; font-size:0.8rem;">
                                <i class="bi bi-file-earmark-text me-1"></i>Quotation (EN)
                            </a>
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

    {{-- Create Sale Modal --}}
    <div class="modal fade" id="createSaleModal" tabindex="-1" aria-labelledby="createSaleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('buy-sale.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createSaleModalLabel">Create Sale — Select Unit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="project_id" class="form-label">Building (Project)</label>
                            <select id="project_id" class="form-select" required>
                                <option value="">-- Select Building --</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="floor_select" class="form-label">Floor</label>
                            <select id="floor_select" class="form-select" disabled required>
                                <option value="">-- Select Floor --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="listing_id" class="form-label">Room</label>
                            <select id="listing_id" name="listing_id" class="form-select" disabled required>
                                <option value="">-- Select Room --</option>
                            </select>
                        </div>
                        <div id="unitPreview" class="card bg-light d-none">
                            <div class="card-body py-2">
                                <h6 class="card-title mb-2">Unit Details</h6>
                                <div class="row small">
                                    <div class="col-6 mb-1"><strong>Room:</strong> <span id="prevRoom">-</span></div>
                                    <div class="col-6 mb-1"><strong>Unit Code:</strong> <span id="prevCode">-</span></div>
                                    <div class="col-6 mb-1"><strong>Type:</strong> <span id="prevType">-</span></div>
                                    <div class="col-6 mb-1"><strong>Area:</strong> <span id="prevArea">-</span> sqm</div>
                                    <div class="col-6 mb-1"><strong>Price:</strong> <span id="prevPrice">-</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark" id="btnCreateSale" disabled>Create Sale</button>
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

    {{-- Appointment Modal --}}
    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="appointmentForm">
                    @csrf
                    <input type="hidden" name="appointment_date" id="appointmentDateInput">
                    <input type="hidden" name="appointment_time" id="appointmentTimeInput">
                    <div class="modal-header">
                        <h5 class="modal-title" id="appointmentModalLabel">
                            <i class="bi bi-calendar-check me-2" style="color:#7c3aed;"></i>Make Appointment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">วันที่นัดหมาย <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="appointmentDatePicker" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">เวลานัดหมาย <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 align-items-center">
                                <select class="form-select" id="appointmentHour" style="max-width:110px;">
                                    @for($h = 0; $h <= 23; $h++)
                                        <option value="{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}">
                                            {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00 น.
                                        </option>
                                    @endfor
                                </select>
                                <span class="text-muted fw-semibold">:</span>
                                <select class="form-select" id="appointmentMinute" style="max-width:100px;">
                                    @foreach([0,5,10,15,20,25,30,35,40,45,50,55] as $m)
                                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                                            {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-check-lg me-1"></i> Confirm Appointment
                        </button>
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
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const projectSelect = document.getElementById('project_id');
    const floorSelect = document.getElementById('floor_select');
    const listingSelect = document.getElementById('listing_id');
    const unitPreview = document.getElementById('unitPreview');
    const btnCreate = document.getElementById('btnCreateSale');
    const floorsEndpointTemplate = "{{ route('buy-sale.api.floors', ['project' => '__PROJECT__'], false) }}";
    const unitsEndpointTemplate = "{{ route('buy-sale.api.units', ['project' => '__PROJECT__', 'floor' => '__FLOOR__'], false) }}";

    if (!projectSelect || !floorSelect || !listingSelect) {
        return;
    }

    let unitsData = [];

    projectSelect.addEventListener('change', function () {
        floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';
        floorSelect.disabled = true;
        listingSelect.innerHTML = '<option value="">-- Select Room --</option>';
        listingSelect.disabled = true;
        unitPreview.classList.add('d-none');
        btnCreate.disabled = true;
        if (!this.value) return;

        const floorsUrl = floorsEndpointTemplate.replace('__PROJECT__', encodeURIComponent(this.value));

        fetch(floorsUrl)
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
            .catch(() => {
                floorSelect.disabled = true;
            });
    });

    floorSelect.addEventListener('change', function () {
        listingSelect.innerHTML = '<option value="">-- Select Room --</option>';
        listingSelect.disabled = true;
        unitPreview.classList.add('d-none');
        btnCreate.disabled = true;
        unitsData = [];
        if (!this.value) return;

        const unitsUrl = unitsEndpointTemplate
            .replace('__PROJECT__', encodeURIComponent(projectSelect.value))
            .replace('__FLOOR__', encodeURIComponent(this.value));

        fetch(unitsUrl)
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
            .catch(() => {
                listingSelect.disabled = true;
            });
    });

    listingSelect.addEventListener('change', function () {
        unitPreview.classList.add('d-none');
        btnCreate.disabled = true;
        if (!this.value) return;
        const unit = unitsData.find(u => u.id == this.value);
        if (unit) {
            document.getElementById('prevRoom').textContent = unit.room_number || '-';
            document.getElementById('prevCode').textContent = unit.unit_code || '-';
            document.getElementById('prevType').textContent = unit.unit_type || '-';
            document.getElementById('prevArea').textContent = unit.area ? Number(unit.area).toLocaleString('en', {minimumFractionDigits: 2}) : '-';
            document.getElementById('prevPrice').textContent = unit.price_per_room ? Number(unit.price_per_room).toLocaleString('en', {minimumFractionDigits: 2}) : '-';
            unitPreview.classList.remove('d-none');
            btnCreate.disabled = false;
        }
    });

    document.getElementById('createSaleModal').addEventListener('hidden.bs.modal', function () {
        projectSelect.value = '';
        floorSelect.innerHTML = '<option value="">-- Select Floor --</option>';
        floorSelect.disabled = true;
        listingSelect.innerHTML = '<option value="">-- Select Room --</option>';
        listingSelect.disabled = true;
        unitPreview.classList.add('d-none');
        btnCreate.disabled = true;
        unitsData = [];
    });

    // Scroll to highlighted card
    const highlight = document.querySelector('.pipeline-card.highlight');
    if (highlight) {
        setTimeout(() => {
            highlight.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 300);
    }

    // Auto-submit filters when project changes or unit code typing pauses
    const filterForm = document.getElementById('pipelineFilterForm');
    if (filterForm) {
        const projectFilter = filterForm.querySelector('[data-filter-project]');
        const unitFilter = filterForm.querySelector('[data-filter-unit]');
        const submitFilters = () => {
            if (filterForm.requestSubmit) {
                filterForm.requestSubmit();
            } else {
                filterForm.submit();
            }
        };

        if (projectFilter) {
            projectFilter.addEventListener('change', submitFilters);
        }

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

    // Appointment modal
    const appointmentModalEl = document.getElementById('appointmentModal');
    const appointmentForm = document.getElementById('appointmentForm');
    const appointmentDatePicker = document.getElementById('appointmentDatePicker');
    const appointmentHour = document.getElementById('appointmentHour');
    const appointmentMinute = document.getElementById('appointmentMinute');
    const appointmentDateInput = document.getElementById('appointmentDateInput');
    const appointmentTimeInput = document.getElementById('appointmentTimeInput');

    if (appointmentModalEl && appointmentForm && window.bootstrap) {
        const appointmentModal = new bootstrap.Modal(appointmentModalEl);

        document.querySelectorAll('.appointment-trigger').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                appointmentForm.action = trigger.dataset.route;
                appointmentDatePicker.value = trigger.dataset.date || '';

                // Pre-fill hour/minute from saved time (HH:MM:SS)
                const savedTime = trigger.dataset.time || '';
                if (savedTime) {
                    const parts = savedTime.split(':');
                    appointmentHour.value = parts[0] || '09';
                    // snap minute to nearest 5
                    const rawMin = parseInt(parts[1] || '0', 10);
                    const snapped = String(Math.round(rawMin / 5) * 5 % 60).padStart(2, '0');
                    appointmentMinute.value = snapped;
                } else {
                    appointmentHour.value = '09';
                    appointmentMinute.value = '00';
                }

                appointmentModal.show();
            });
        });

        appointmentForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!appointmentDatePicker.value) return;
            appointmentDateInput.value = appointmentDatePicker.value;
            appointmentTimeInput.value = appointmentHour.value + ':' + appointmentMinute.value + ':00';
            appointmentForm.submit();
        });

        appointmentModalEl.addEventListener('hidden.bs.modal', () => {
            appointmentDatePicker.value = '';
            appointmentHour.value = '09';
            appointmentMinute.value = '00';
        });
    }
});
</script>
@endsection
