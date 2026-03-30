@extends('layouts.app')

@section('title', 'Report')

@section('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem; }
    .page-header h3 { font-weight: 700; margin: 0; }

    /* Filter strip */
    .view-toggle-wrapper { display: flex; justify-content: flex-end; margin-bottom: 1.25rem; }
    @media (max-width: 767px) { .view-toggle-wrapper { justify-content: flex-start; } }
    .filter-strip .form-select {
        font-size: 0.82rem; border-radius: var(--radius-sm); height: 36px;
        padding: 0.3rem 0.65rem; min-width: 130px;
    }
    .view-toggle { display: inline-flex; border: 1px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; margin-left: auto; }
    .view-toggle button {
        border: none; background: var(--surface); color: var(--text-mid); font-size: 0.78rem;
        font-weight: 600; padding: 0.4rem 0.85rem; cursor: pointer; transition: all 0.15s;
    }
    .view-toggle button:hover { background: rgba(42,139,146,0.06); }
    .view-toggle button.active { background: var(--primary); color: #fff; }

    .card-filter {
        display: flex; flex-direction: column; gap: 0.35rem; margin-left: auto; text-align: right;
    }
    .card-filter select {
        font-size: 0.78rem; height: 32px; padding: 0.2rem 0.5rem;
        border-radius: var(--radius-sm);
    }

    /* Section card */
    .section-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius); overflow: hidden; margin-bottom: 1.25rem;
    }
    .section-card-header {
        display: flex; align-items: center; gap: 0.5rem;
        padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border);
        font-weight: 700; font-size: 0.88rem; color: var(--text-dark);
        background: var(--bg, #faf8f5);
    }
    .section-card-header i { color: var(--primary); font-size: 1rem; }
    .section-card-body { padding: 1.25rem; }
    @media (max-width: 768px) {
        .section-card-header { flex-wrap: wrap; gap: 0.4rem; }
        .card-filter { width: 100%; text-align: left; }
    }

    /* Donut layout */
    .donut-section { display: flex; gap: 1.5rem; align-items: center; flex-direction: column; }
    .donut-canvas-wrap { flex-shrink: 0; width: 220px; height: 220px; position: relative; }
    .donut-canvas-wrap canvas { width: 100% !important; height: 100% !important; }
    .donut-center {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        text-align: center; pointer-events: none;
    }
    .donut-center-value { font-size: 1.15rem; font-weight: 800; color: var(--text-dark); line-height: 1.2; }
    .donut-center-sub { font-size: 0.72rem; font-weight: 600; color: var(--text-light); margin-top: 2px; }
    .donut-toggle { display: inline-flex; border: 1px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; margin-left: 0.5rem; }
    .donut-toggle button {
        border: none; background: var(--surface); color: var(--text-mid); font-size: 0.68rem;
        font-weight: 600; padding: 0.25rem 0.6rem; cursor: pointer; transition: all 0.15s;
    }
    .donut-toggle button:hover { background: rgba(42,139,146,0.06); }
    .donut-toggle button.active { background: var(--primary); color: #fff; }
    .donut-list { flex: 1; width: 100%; }
    .donut-item { display: flex; align-items: center; gap: 0.65rem; padding: 0.45rem 0; font-size: 0.82rem; justify-content: space-between; }
    .donut-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .donut-label { flex: 1; min-width: 0; font-weight: 600; color: var(--text-dark); }
    .donut-label small { font-weight: 400; color: var(--text-light); margin-left: 4px; }
    .donut-value { font-weight: 700; color: var(--text-dark); white-space: nowrap; }

    /* Ranked list */
    .ranked-list { list-style: none; margin: 0; padding: 0; }
    .ranked-item {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.7rem 0; border-bottom: 1px solid rgba(0,0,0,0.04);
    }
    .ranked-item:last-child { border-bottom: none; }
    .rank-num {
        width: 28px; height: 28px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.72rem; font-weight: 800; flex-shrink: 0;
    }
    .rank-1 { background: #fef3c7; color: #b45309; }
    .rank-2 { background: #f1f5f9; color: #475569; }
    .rank-3 { background: #fef2f2; color: #b91c1c; }
    .rank-default { background: var(--bg, #f5f3ef); color: var(--text-mid); }
    .rank-avatar {
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.78rem; font-weight: 700; color: #fff; flex-shrink: 0;
    }
    .rank-info { flex: 1; min-width: 0; }
    .rank-name { font-weight: 700; font-size: 0.84rem; color: var(--text-dark); }
    .rank-team { font-size: 0.72rem; color: var(--text-light); }
    .rank-val { text-align: right; }
    .rank-val-main { font-weight: 800; font-size: 0.88rem; color: var(--text-dark); }
    .rank-val-sub { font-size: 0.68rem; color: var(--text-light); }

    /* Horizontal bars */
    .hbar-item { margin-bottom: 0.75rem; }
    .hbar-item:last-child { margin-bottom: 0; }
    .hbar-top { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px; }
    .hbar-label { font-size: 0.82rem; font-weight: 600; color: var(--text-dark); }
    .hbar-value { font-size: 0.78rem; font-weight: 700; color: var(--text-mid); }
    .hbar-track { height: 22px; background: rgba(0,0,0,0.04); border-radius: 4px; overflow: hidden; }
    .hbar-fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }

    /* Customer stat cards */
    .cust-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; }
    .cust-card {
        padding: 1rem 1.25rem; border-radius: var(--radius-sm);
        border: 1px solid var(--border);
    }
    .cust-card-value { font-size: 1.5rem; font-weight: 800; line-height: 1.2; }
    .cust-card-sub { font-size: 0.82rem; font-weight: 600; color: var(--text-mid); margin-top: 2px; }
    .cust-card-label { font-size: 0.72rem; font-weight: 600; color: var(--text-light); margin-top: 6px; }

    /* Chart container */
    .chart-container { position: relative; height: 260px; }

    /* Category headers */
    .category-header {
        display: flex; align-items: center; gap: 0.5rem;
        margin: 1.75rem 0 1rem; padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--primary); font-size: 0.92rem;
        font-weight: 800; color: var(--text-dark);
    }
    .category-header:first-of-type { margin-top: 0; }
    .category-header i { color: var(--primary); font-size: 1.05rem; }

    .empty-state {
        text-align: center; padding: 2rem 1rem; color: var(--text-light); font-size: 0.85rem;
    }
    .empty-state i { font-size: 1.75rem; display: block; margin-bottom: 0.5rem; }

    @media (max-width: 767px) {
        .donut-section { flex-direction: column; align-items: center; }
        .donut-canvas-wrap { width: 150px; height: 150px; }
        .cust-cards { grid-template-columns: 1fr; }
    }
</style>
@endsection

@php
    $month = $filters['month'];
    $year = $filters['year'];
    $teamId = $filters['teamId'];
    $view = $filters['view'];
    $monthLabel = \Carbon\Carbon::create($year, $month)->format('M Y');

    $donutColors = ['#2A8B92', '#f79009', '#7c3aed', '#0ba5ec', '#12b76a', '#dc3545', '#6366f1', '#ec4899'];
    $avatarColors = ['#2A8B92', '#7c3aed', '#f79009', '#0ba5ec', '#12b76a', '#dc3545'];
@endphp

@section('content')
    <div class="page-header">
        <h3>Report</h3>
        @permission('report.export')
        <a id="exportPdfBtn" href="#" class="btn btn-sm" style="background: var(--primary); color: #fff; font-size: 0.82rem; font-weight: 600; padding: 0.4rem 1rem; border-radius: var(--radius-sm);">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>
        @endpermission
    </div>

    <div class="view-toggle-wrapper">
        <div class="view-toggle" id="viewToggle">
            <button type="button" data-view="transferred" class="{{ $view === 'transferred' ? 'active' : '' }}">Transferred</button>
            <button type="button" data-view="active" class="{{ $view === 'active' ? 'active' : '' }}">Active</button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- ── SALES PERFORMANCE ──────────────────────────────── --}}
    <div class="category-header"><i class="bi bi-bar-chart-line"></i> Sales Performance</div>

    <div class="row g-3 mb-3">
        {{-- ── Sale Value by Team (donut) ────────────────────── --}}
        <div class="col-lg-6">
            <div class="section-card mb-0">
                <div class="section-card-header">
                    <span><i class="bi bi-people"></i> Sale Value by Team</span>
                    <div class="donut-toggle" data-target="team">
                        <button type="button" data-mode="value" class="active">Value</button>
                        <button type="button" data-mode="units">Units</button>
                    </div>
                    <form class="card-filter" method="GET" action="{{ route('report.index') }}">
                        <select name="team_chart_year" onchange="this.form.submit()" aria-label="Team chart year">
                            <option value="" disabled>Year</option>
                            @for($y = $teamChartYear - 2; $y <= $teamChartYear + 1; $y++)
                                <option value="{{ $y }}" {{ $teamChartYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <select name="team_chart_month" onchange="this.form.submit()" aria-label="Team chart month">
                            <option value="" disabled>Month</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $teamChartMonth == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endfor
                        </select>
                        <input type="hidden" name="person_chart_month" value="{{ $personChartMonth }}">
                        <input type="hidden" name="person_chart_year" value="{{ $personChartYear }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="view" value="{{ $view }}">
                        @if($teamId)
                            <input type="hidden" name="team_id" value="{{ $teamId }}">
                        @endif
                    </form>
                </div>
                <div class="section-card-body">
                    @if($teamChart->count())
                        <div class="donut-section">
                            <div class="donut-canvas-wrap">
                                <canvas id="teamDonut"></canvas>
                                <div class="donut-center" id="teamDonutCenter"
                                     data-value-text="฿{{ number_format($teamChart->sum('total_value'), 0) }}"
                                     data-units-text="{{ number_format($teamChart->sum('deal_count')) }} units">
                                    <div class="donut-center-value">฿{{ number_format($teamChart->sum('total_value'), 0) }}</div>
                                </div>
                            </div>
                            <div class="donut-list" id="teamDonutList">
                                @foreach($teamChart as $i => $row)
                                    <div class="donut-item" data-value-text="฿{{ number_format($row->total_value, 0) }}" data-units-text="{{ $row->deal_count }} units">
                                        <span class="donut-dot" style="background: {{ $donutColors[$i % count($donutColors)] }};"></span>
                                        <span class="donut-label">{{ $row->team_name }} <small>{{ $row->deal_count }} deals</small></span>
                                        <span class="donut-value">฿{{ number_format($row->total_value, 0) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="empty-state"><i class="bi bi-inbox"></i> No team data for {{ $monthLabel }}.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Sale Value by Person (donut) ──────────────────── --}}
        <div class="col-lg-6">
            <div class="section-card mb-0">
                <div class="section-card-header">
                    <span><i class="bi bi-person"></i> Sale Value by Person</span>
                    <div class="donut-toggle" data-target="person">
                        <button type="button" data-mode="value" class="active">Value</button>
                        <button type="button" data-mode="units">Units</button>
                    </div>
                    <form class="card-filter" method="GET" action="{{ route('report.index') }}">
                        <select name="person_chart_year" onchange="this.form.submit()" aria-label="Person chart year">
                            <option value="" disabled>Year</option>
                            @for($y = $personChartYear - 2; $y <= $personChartYear + 1; $y++)
                                <option value="{{ $y }}" {{ $personChartYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <select name="person_chart_month" onchange="this.form.submit()" aria-label="Person chart month">
                            <option value="" disabled>Month</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $personChartMonth == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endfor
                        </select>
                        <input type="hidden" name="team_chart_month" value="{{ $teamChartMonth }}">
                        <input type="hidden" name="team_chart_year" value="{{ $teamChartYear }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="view" value="{{ $view }}">
                        @if($teamId)
                            <input type="hidden" name="team_id" value="{{ $teamId }}">
                        @endif
                    </form>
                </div>
                <div class="section-card-body">
                    @if($saleChart->count())
                        <div class="donut-section">
                            <div class="donut-canvas-wrap">
                                <canvas id="saleDonut"></canvas>
                                <div class="donut-center" id="personDonutCenter"
                                     data-value-text="฿{{ number_format($saleChart->sum('total_value'), 0) }}"
                                     data-units-text="{{ number_format($saleChart->sum('deal_count')) }} units">
                                    <div class="donut-center-value">฿{{ number_format($saleChart->sum('total_value'), 0) }}</div>
                                </div>
                            </div>
                            <div class="donut-list" id="personDonutList">
                                @foreach($saleChart->take(8) as $i => $row)
                                    <div class="donut-item" data-value-text="฿{{ number_format($row->total_value, 0) }}" data-units-text="{{ $row->deal_count }} units">
                                        <span class="donut-dot" style="background: {{ $donutColors[$i % count($donutColors)] }};"></span>
                                        <span class="donut-label">{{ $row->name }} <small>{{ $row->team_name ?? '' }}</small></span>
                                        <span class="donut-value">฿{{ number_format($row->total_value, 0) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="empty-state"><i class="bi bi-inbox"></i> No person data for {{ $monthLabel }}.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Top 5 Ranked List ─────────────────────────────── --}}
    <div class="section-card">
        <div class="section-card-header">
            <i class="bi bi-trophy"></i> Top 5 Sale Performance
            <span style="font-size: 0.72rem; font-weight: 400; color: var(--text-light); margin-left: auto;">
                {{ $view === 'active' ? 'Active Deals' : 'Transferred · ' . $monthLabel }}
            </span>
        </div>
        <div class="section-card-body" style="padding: 0.75rem 1.25rem;">
            @if($top5->count())
                <ul class="ranked-list">
                    @foreach($top5 as $i => $row)
                        @php $initials = collect(explode(' ', $row->name))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->join(''); @endphp
                        <li class="ranked-item">
                            <span class="rank-num {{ $i < 3 ? 'rank-' . ($i + 1) : 'rank-default' }}">{{ $i + 1 }}</span>
                            <span class="rank-avatar" style="background: {{ $avatarColors[$i % count($avatarColors)] }};">{{ $initials }}</span>
                            <div class="rank-info">
                                <div class="rank-name">{{ $row->name }}</div>
                                <div class="rank-team">{{ $row->team_name ?? '—' }}</div>
                            </div>
                            <div class="rank-val">
                                <div class="rank-val-main">฿{{ number_format($row->total_value, 0) }}</div>
                                <div class="rank-val-sub">{{ $row->deal_count }} deals</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="empty-state"><i class="bi bi-inbox"></i> No data.</div>
            @endif
        </div>
    </div>

    {{-- ── Unit Type Horizontal Bars ─────────────────────── --}}
    <div class="section-card">
        <div class="section-card-header">
            <i class="bi bi-grid-3x3-gap"></i> Unit Type Breakdown
        </div>
        <div class="section-card-body">
            @if($unitTypeBar->count())
                @php $barColors = ['#2A8B92', '#7c3aed', '#f79009', '#0ba5ec', '#12b76a', '#dc3545']; @endphp
                @foreach($unitTypeBar as $i => $row)
                    @php $pct = round($row->cnt / $unitTypeMax * 100); @endphp
                    <div class="hbar-item">
                        <div class="hbar-top">
                            <span class="hbar-label">{{ $row->unit_type }}</span>
                            <span class="hbar-value">{{ $row->cnt }} units · ฿{{ number_format($row->val, 0) }}</span>
                        </div>
                        <div class="hbar-track">
                            <div class="hbar-fill" style="width: {{ $pct }}%; background: {{ $barColors[$i % count($barColors)] }};"></div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state"><i class="bi bi-inbox"></i> No unit type data.</div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- ── CUSTOMER ANALYSIS ──────────────────────────────── --}}
    <div class="category-header"><i class="bi bi-people"></i> Customer Analysis</div>

    {{-- ── Customer Nationality (Thai vs Foreign) ─────────── --}}
    <div class="section-card">
        <div class="section-card-header">
            <i class="bi bi-globe-americas"></i> Customer Nationality
        </div>
        <div class="section-card-body">
            @php
                $thaiNat = $nationalitySplit->get('Thai');
                $foreignNat = $nationalitySplit->get('Foreign');
                $thaiCount = $thaiNat->deal_count ?? 0;
                $thaiValue = $thaiNat->total_value ?? 0;
                $foreignCount = $foreignNat->deal_count ?? 0;
                $foreignValue = $foreignNat->total_value ?? 0;
                $natTotal = $thaiCount + $foreignCount;
            @endphp
            <div class="cust-cards">
                <div class="cust-card" style="background: rgba(42,139,146,0.04);">
                    <div class="cust-card-value" style="color: var(--primary);">{{ number_format($thaiCount) }}</div>
                    <div class="cust-card-sub">฿{{ number_format($thaiValue, 0) }}</div>
                    <div class="cust-card-label">Thai (บัตรประชาชน)</div>
                </div>
                <div class="cust-card" style="background: rgba(124,58,237,0.04);">
                    <div class="cust-card-value" style="color: #7c3aed;">{{ number_format($foreignCount) }}</div>
                    <div class="cust-card-sub">฿{{ number_format($foreignValue, 0) }}</div>
                    <div class="cust-card-label">Foreign (Passport)</div>
                </div>
            </div>
            @if($natTotal > 0)
                <div style="display: flex; justify-content: center;">
                    <div style="width: 150px; height: 150px; position: relative;">
                        <canvas id="natDonut"></canvas>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Customer Type Split ───────────────────────────── --}}
    <div class="section-card">
        <div class="section-card-header">
            <i class="bi bi-credit-card-2-front"></i> Customer Type
        </div>
        <div class="section-card-body">
            @php
                $bl = $customerSplit->bank_loan_count ?? 0;
                $cc = $customerSplit->cash_count ?? 0;
                $total = $bl + $cc;
            @endphp
            <div class="cust-cards">
                <div class="cust-card" style="background: rgba(42,139,146,0.04);">
                    <div class="cust-card-value" style="color: var(--primary);">{{ number_format($bl) }}</div>
                    <div class="cust-card-sub">฿{{ number_format($customerSplit->bank_loan_value ?? 0, 0) }}</div>
                    <div class="cust-card-label">Bank Loan</div>
                </div>
                <div class="cust-card" style="background: rgba(247,144,9,0.04);">
                    <div class="cust-card-value" style="color: #f79009;">{{ number_format($cc) }}</div>
                    <div class="cust-card-sub">฿{{ number_format($customerSplit->cash_value ?? 0, 0) }}</div>
                    <div class="cust-card-label">Cash Transfer</div>
                </div>
            </div>
            @if($total > 0)
                <div style="display: flex; justify-content: center;">
                    <div style="width: 150px; height: 150px; position: relative;">
                        <canvas id="custDonut"></canvas>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Payment by Nationality (donut pair) ─────────── --}}
    @php
        $payNatMap = [];
        foreach ($paymentByNationality as $row) {
            $payNatMap[$row->nationality][$row->payment_type] = $row;
        }
        $thaiBankCnt = $payNatMap['Thai']['Bank Loan']->cnt ?? 0;
        $thaiBankVal = $payNatMap['Thai']['Bank Loan']->val ?? 0;
        $thaiCashCnt = $payNatMap['Thai']['Cash Transfer']->cnt ?? 0;
        $thaiCashVal = $payNatMap['Thai']['Cash Transfer']->val ?? 0;
        $forBankCnt = $payNatMap['Foreign']['Bank Loan']->cnt ?? 0;
        $forBankVal = $payNatMap['Foreign']['Bank Loan']->val ?? 0;
        $forCashCnt = $payNatMap['Foreign']['Cash Transfer']->cnt ?? 0;
        $forCashVal = $payNatMap['Foreign']['Cash Transfer']->val ?? 0;
        $payNatTotalCnt = $thaiBankCnt + $thaiCashCnt + $forBankCnt + $forCashCnt;
        $payNatTotalVal = $thaiBankVal + $thaiCashVal + $forBankVal + $forCashVal;
    @endphp
    <div class="row g-3 mb-3">
        {{-- Left: Units --}}
        <div class="col-lg-6">
            <div class="section-card mb-0">
                <div class="section-card-header">
                    <span><i class="bi bi-pie-chart"></i> Payment × Nationality (Units)</span>
                </div>
                <div class="section-card-body">
                    @if($payNatTotalCnt > 0)
                        <div class="donut-section">
                            <div class="donut-canvas-wrap">
                                <canvas id="payNatUnitsDonut"></canvas>
                                <div class="donut-center">
                                    <div class="donut-center-value">{{ number_format($payNatTotalCnt) }}</div>
                                    <div class="donut-center-sub">units</div>
                                </div>
                            </div>
                            <div class="donut-list">
                                <div class="donut-item">
                                    <span class="donut-dot" style="background: #2A8B92;"></span>
                                    <span class="donut-label">Thai · Bank Loan</span>
                                    <span class="donut-value">{{ number_format($thaiBankCnt) }}</span>
                                </div>
                                <div class="donut-item">
                                    <span class="donut-dot" style="background: #7c3aed;"></span>
                                    <span class="donut-label">Thai · Cash</span>
                                    <span class="donut-value">{{ number_format($thaiCashCnt) }}</span>
                                </div>
                                <div class="donut-item">
                                    <span class="donut-dot" style="background: #f79009;"></span>
                                    <span class="donut-label">Foreign · Bank Loan</span>
                                    <span class="donut-value">{{ number_format($forBankCnt) }}</span>
                                </div>
                                <div class="donut-item">
                                    <span class="donut-dot" style="background: #0ba5ec;"></span>
                                    <span class="donut-label">Foreign · Cash</span>
                                    <span class="donut-value">{{ number_format($forCashCnt) }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="empty-state"><i class="bi bi-inbox"></i> No data.</div>
                    @endif
                </div>
            </div>
        </div>
        {{-- Right: Value --}}
        <div class="col-lg-6">
            <div class="section-card mb-0">
                <div class="section-card-header">
                    <span><i class="bi bi-pie-chart"></i> Payment × Nationality (Value)</span>
                </div>
                <div class="section-card-body">
                    @if($payNatTotalVal > 0)
                        <div class="donut-section">
                            <div class="donut-canvas-wrap">
                                <canvas id="payNatValueDonut"></canvas>
                                <div class="donut-center">
                                    <div class="donut-center-value">฿{{ number_format($payNatTotalVal, 0) }}</div>
                                </div>
                            </div>
                            <div class="donut-list">
                                <div class="donut-item">
                                    <span class="donut-dot" style="background: #2A8B92;"></span>
                                    <span class="donut-label">Thai · Bank Loan</span>
                                    <span class="donut-value">฿{{ number_format($thaiBankVal, 0) }}</span>
                                </div>
                                <div class="donut-item">
                                    <span class="donut-dot" style="background: #7c3aed;"></span>
                                    <span class="donut-label">Thai · Cash</span>
                                    <span class="donut-value">฿{{ number_format($thaiCashVal, 0) }}</span>
                                </div>
                                <div class="donut-item">
                                    <span class="donut-dot" style="background: #f79009;"></span>
                                    <span class="donut-label">Foreign · Bank Loan</span>
                                    <span class="donut-value">฿{{ number_format($forBankVal, 0) }}</span>
                                </div>
                                <div class="donut-item">
                                    <span class="donut-dot" style="background: #0ba5ec;"></span>
                                    <span class="donut-label">Foreign · Cash</span>
                                    <span class="donut-value">฿{{ number_format($forCashVal, 0) }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="empty-state"><i class="bi bi-inbox"></i> No data.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- ── MARKETING BUDGET ────────────────────────────────── --}}
    @permission('report.manage_budget')
    <div class="category-header"><i class="bi bi-megaphone"></i> Marketing Budget</div>

    {{-- Summary Cards --}}
    <div class="cust-cards" id="budgetSummaryCards" style="{{ $budgets->count() ? '' : 'display:none;' }}">
        <div class="cust-card" style="background: rgba(42,139,146,0.04);">
            <div class="cust-card-value" style="color: var(--primary);" id="summaryOnline">฿{{ number_format($budgets->sum('budget_marketing_online'), 0) }}</div>
            <div class="cust-card-label">Total Online Budget</div>
        </div>
        <div class="cust-card" style="background: rgba(124,58,237,0.04);">
            <div class="cust-card-value" style="color: #7c3aed;" id="summaryOffline">฿{{ number_format($budgets->sum('budget_marketing_offline'), 0) }}</div>
            <div class="cust-card-label">Total Offline Budget</div>
        </div>
        <div class="cust-card" style="background: rgba(247,144,9,0.04);">
            <div class="cust-card-value" style="color: #f79009;" id="summaryTotal">฿{{ number_format($budgets->sum('budget_marketing_online') + $budgets->sum('budget_marketing_offline'), 0) }}</div>
            <div class="cust-card-label">Total Budget</div>
        </div>
    </div>

    {{-- Weekly Budget Table --}}
    <div class="section-card">
        <div class="section-card-header">
            <i class="bi bi-cash-stack"></i> Weekly Marketing Budget
            <span style="font-size: 0.72rem; font-weight: 400; color: var(--text-light); margin-left: auto;">
                {{ $monthLabel }}
            </span>
        </div>
        <div class="section-card-body">
            {{-- Toast --}}
            <div id="budgetToast" style="display:none; font-size: 0.82rem; padding: 0.5rem 1rem; margin-bottom: 1rem; border-radius: var(--radius-sm); background: rgba(18,183,106,0.08); color: #059669; border: 1px solid rgba(18,183,106,0.2);"></div>

            {{-- Table container (rendered by JS) --}}
            <div id="budgetTableWrap"></div>

            {{-- Add / Edit Form --}}
            <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem;" id="budgetFormTitle">
                <i class="bi bi-plus-circle" style="color: var(--primary);"></i> เพิ่ม Budget รายสัปดาห์
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label style="font-size: 0.78rem; font-weight: 600; color: var(--text-mid); margin-bottom: 4px; display: block;">สัปดาห์</label>
                    <select id="budgetWeek" class="form-select" style="font-size: 0.85rem;">
                        <option value="1">Week 1</option>
                        <option value="2">Week 2</option>
                        <option value="3">Week 3</option>
                        <option value="4">Week 4</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label style="font-size: 0.78rem; font-weight: 600; color: var(--text-mid); margin-bottom: 4px; display: block;">
                        <i class="bi bi-globe" style="color: var(--primary);"></i> Online (฿)
                    </label>
                    <input type="number" step="0.01" min="0" id="budgetOnline" class="form-control" style="font-size: 0.85rem;" placeholder="0.00">
                </div>
                <div class="col-md-4">
                    <label style="font-size: 0.78rem; font-weight: 600; color: var(--text-mid); margin-bottom: 4px; display: block;">
                        <i class="bi bi-shop" style="color: #7c3aed;"></i> Offline (฿)
                    </label>
                    <input type="number" step="0.01" min="0" id="budgetOffline" class="form-control" style="font-size: 0.85rem;" placeholder="0.00">
                </div>
            </div>

            <div style="margin-top: 0.75rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" id="budgetCancelBtn" class="btn btn-sm" style="font-size: 0.82rem; padding: 0.4rem 1rem; border: 1px solid var(--border); color: var(--text-mid); border-radius: var(--radius-sm); background: transparent; display: none;"
                        onclick="budgetCancel()">
                    Cancel
                </button>
                <button type="button" id="budgetSaveBtn" class="btn btn-sm" style="background: var(--primary); color: #fff; font-size: 0.82rem; font-weight: 600; padding: 0.4rem 1.25rem; border-radius: var(--radius-sm);"
                        onclick="budgetSave()">
                    <i class="bi bi-check-lg"></i> Save
                </button>
            </div>
        </div>
    </div>
    @endpermission

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function() {
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#6b8c93';

    const colors = @json($donutColors);

    // View toggle
    const filterForm = document.querySelector('form.filter-strip');
    const viewInput = document.getElementById('viewInput');
    document.querySelectorAll('#viewToggle button').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!filterForm || !viewInput) return;
            viewInput.value = this.dataset.view;
            filterForm.submit();
        });
    });

    // ── Donut config ──
    const donutOpts = (fmt) => ({
        responsive: true, maintainAspectRatio: true, cutout: '78%',
        plugins: { legend: { display: false }, tooltip: {
            backgroundColor: '#1a2e35', padding: 10, cornerRadius: 8,
            callbacks: { label: ctx => fmt === 'value' ? '฿' + Number(ctx.raw).toLocaleString() : ctx.raw + ' units' }
        }}
    });

    const chartRefs = {};
    const chartData = {};

    // ── Team Donut ──
    @if($teamChart->count())
    chartData.team = {
        value: @json($teamChart->pluck('total_value')->toArray()),
        units: @json($teamChart->pluck('deal_count')->toArray()),
        labels: @json($teamChart->pluck('team_name')->toArray()),
        colors: colors.slice(0, {{ $teamChart->count() }})
    };
    chartRefs.team = new Chart(document.getElementById('teamDonut'), {
        type: 'doughnut',
        data: {
            labels: chartData.team.labels,
            datasets: [{ data: chartData.team.value, backgroundColor: chartData.team.colors, borderWidth: 0, hoverOffset: 4 }]
        },
        options: donutOpts('value')
    });
    @endif

    // ── Sale Donut ──
    @if($saleChart->count())
    chartData.person = {
        value: @json($saleChart->take(8)->pluck('total_value')->toArray()),
        units: @json($saleChart->take(8)->pluck('deal_count')->toArray()),
        labels: @json($saleChart->take(8)->pluck('name')->toArray()),
        colors: colors.slice(0, {{ min($saleChart->count(), 8) }})
    };
    chartRefs.person = new Chart(document.getElementById('saleDonut'), {
        type: 'doughnut',
        data: {
            labels: chartData.person.labels,
            datasets: [{ data: chartData.person.value, backgroundColor: chartData.person.colors, borderWidth: 0, hoverOffset: 4 }]
        },
        options: donutOpts('value')
    });
    @endif

    // ── Donut Toggle (Value / Units) ──
    document.querySelectorAll('.donut-toggle').forEach(toggle => {
        toggle.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = toggle.dataset.target; // 'team' or 'person'
                const mode = this.dataset.mode;       // 'value' or 'units'

                // Update active button
                toggle.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Update chart data
                const chart = chartRefs[target];
                const data = chartData[target];
                if (chart && data) {
                    chart.data.datasets[0].data = data[mode];
                    chart.options.plugins.tooltip.callbacks.label = mode === 'value'
                        ? ctx => '฿' + Number(ctx.raw).toLocaleString()
                        : ctx => ctx.raw + ' units';
                    chart.update();
                }

                // Update center text
                const centerId = target === 'team' ? 'teamDonutCenter' : 'personDonutCenter';
                const center = document.getElementById(centerId);
                if (center) {
                    center.querySelector('.donut-center-value').textContent = center.dataset[mode + 'Text'];
                }

                // Update list values
                const listId = target === 'team' ? 'teamDonutList' : 'personDonutList';
                document.querySelectorAll('#' + listId + ' .donut-item').forEach(item => {
                    item.querySelector('.donut-value').textContent = item.dataset[mode + 'Text'];
                });
            });
        });
    });

    // ── Nationality Donut ──
    @php
        $thaiNat = $nationalitySplit->get('Thai');
        $foreignNat = $nationalitySplit->get('Foreign');
        $natThaiCount = $thaiNat->deal_count ?? 0;
        $natForeignCount = $foreignNat->deal_count ?? 0;
    @endphp
    @if(($natThaiCount + $natForeignCount) > 0)
    new Chart(document.getElementById('natDonut'), {
        type: 'doughnut',
        data: {
            labels: ['Thai', 'Foreign'],
            datasets: [{
                data: [{{ $natThaiCount }}, {{ $natForeignCount }}],
                backgroundColor: ['#2A8B92', '#7c3aed'],
                borderWidth: 0, hoverOffset: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true, cutout: '60%',
            plugins: { legend: { display: false }, tooltip: {
                backgroundColor: '#1a2e35', padding: 10, cornerRadius: 8
            }}
        }
    });
    @endif

    // ── Customer Donut ──
    @php $bl = $customerSplit->bank_loan_count ?? 0; $cc = $customerSplit->cash_count ?? 0; @endphp
    @if(($bl + $cc) > 0)
    new Chart(document.getElementById('custDonut'), {
        type: 'doughnut',
        data: {
            labels: ['Bank Loan', 'Cash Transfer'],
            datasets: [{
                data: [{{ $bl }}, {{ $cc }}],
                backgroundColor: ['#2A8B92', '#f79009'],
                borderWidth: 0, hoverOffset: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true, cutout: '60%',
            plugins: { legend: { display: false }, tooltip: {
                backgroundColor: '#1a2e35', padding: 10, cornerRadius: 8
            }}
        }
    });
    @endif

    // ── Payment × Nationality Donuts (Units + Value) ──
    const payNatColors = ['#2A8B92', '#7c3aed', '#f79009', '#0ba5ec'];
    const payNatLabels = ['Thai · Bank Loan', 'Thai · Cash', 'Foreign · Bank Loan', 'Foreign · Cash'];

    @if($payNatTotalCnt > 0)
    new Chart(document.getElementById('payNatUnitsDonut'), {
        type: 'doughnut',
        data: {
            labels: payNatLabels,
            datasets: [{ data: [{{ $thaiBankCnt }}, {{ $thaiCashCnt }}, {{ $forBankCnt }}, {{ $forCashCnt }}], backgroundColor: payNatColors, borderWidth: 0, hoverOffset: 4 }]
        },
        options: donutOpts('units')
    });
    @endif

    @if($payNatTotalVal > 0)
    new Chart(document.getElementById('payNatValueDonut'), {
        type: 'doughnut',
        data: {
            labels: payNatLabels,
            datasets: [{ data: [{{ $thaiBankVal }}, {{ $thaiCashVal }}, {{ $forBankVal }}, {{ $forCashVal }}], backgroundColor: payNatColors, borderWidth: 0, hoverOffset: 4 }]
        },
        options: donutOpts('value')
    });
    @endif

    // ── Export PDF button ──
    document.getElementById('exportPdfBtn').href =
        '{{ route("report.export-pdf") }}?month={{ $month }}&year={{ $year }}&view={{ $view }}'
        + '&team_chart_month={{ $teamChartMonth }}&team_chart_year={{ $teamChartYear }}'
        + '&person_chart_month={{ $personChartMonth }}&person_chart_year={{ $personChartYear }}'
        @if($teamId) + '&team_id={{ $teamId }}' @endif
    ;
    // ── Budget AJAX ──
    const canManageBudget = {{ auth()->user()->hasPermission('report.manage_budget') ? 'true' : 'false' }};
    const budgetState = { budgets: @json($budgets), editing: false };
    const csrfToken = '{{ csrf_token() }}';
    const budgetYear = {{ $year }};
    const budgetMonth = {{ $month }};

    function fmt(n) { return Number(n).toLocaleString('en-US', { maximumFractionDigits: 0 }); }

    function renderBudgetTable() {
        const b = budgetState.budgets;
        const wrap = document.getElementById('budgetTableWrap');
        const cards = document.getElementById('budgetSummaryCards');

        if (!b.length) { wrap.innerHTML = ''; cards.style.display = 'none'; budgetUpdateWeekOpts(); return; }

        const totOn = b.reduce((s, r) => s + parseFloat(r.budget_marketing_online), 0);
        const totOff = b.reduce((s, r) => s + parseFloat(r.budget_marketing_offline), 0);

        cards.style.display = '';
        document.getElementById('summaryOnline').textContent = '฿' + fmt(totOn);
        document.getElementById('summaryOffline').textContent = '฿' + fmt(totOff);
        document.getElementById('summaryTotal').textContent = '฿' + fmt(totOn + totOff);

        const thStyle = 'font-size:0.72rem;text-transform:uppercase;color:var(--text-light);font-weight:700;';
        let html = `<table class="table table-sm" style="font-size:0.82rem;margin-bottom:1.25rem;">
            <thead><tr style="border-bottom:2px solid var(--border);">
                <th style="${thStyle}">Week</th>
                <th class="text-right" style="${thStyle}">Online (฿)</th>
                <th class="text-right" style="${thStyle}">Offline (฿)</th>
                <th class="text-right" style="${thStyle}">Total (฿)</th>
                ${canManageBudget ? '<th style="width:80px;"></th>' : ''}
            </tr></thead><tbody>`;

        b.forEach(r => {
            const on = parseFloat(r.budget_marketing_online);
            const off = parseFloat(r.budget_marketing_offline);
            html += `<tr>
                <td style="font-weight:600;">Week ${r.week}</td>
                <td class="text-right">${fmt(on)}</td>
                <td class="text-right">${fmt(off)}</td>
                <td class="text-right" style="font-weight:700;">${fmt(on + off)}</td>
                ${canManageBudget ? `<td class="text-right">
                    <button type="button" class="btn btn-sm" style="font-size:0.72rem;padding:0.15rem 0.5rem;border:1px solid var(--primary);color:var(--primary);border-radius:var(--radius-sm);background:transparent;margin-right:2px;"
                            onclick="budgetEdit(${r.week},${on},${off})"><i class="bi bi-pencil"></i></button>
                    <button type="button" class="btn btn-sm" style="font-size:0.72rem;padding:0.15rem 0.5rem;border:1px solid #dc3545;color:#dc3545;border-radius:var(--radius-sm);background:transparent;"
                            onclick="budgetDelete(${r.id},${r.week})"><i class="bi bi-trash"></i></button>
                </td>` : ''}
            </tr>`;
        });

        html += `<tr style="border-top:2px solid var(--border);">
            <td style="font-weight:700;">Total</td>
            <td class="text-right" style="font-weight:700;">${fmt(totOn)}</td>
            <td class="text-right" style="font-weight:700;">${fmt(totOff)}</td>
            <td class="text-right" style="font-weight:700;">${fmt(totOn + totOff)}</td>
            ${canManageBudget ? '<td></td>' : ''}</tr></tbody></table>`;

        wrap.innerHTML = html;
        budgetUpdateWeekOpts();
    }

    function budgetUpdateWeekOpts() {
        const sel = document.getElementById('budgetWeek');
        const used = budgetState.budgets.map(r => r.week);
        Array.from(sel.options).forEach(opt => {
            const w = parseInt(opt.value);
            opt.disabled = used.includes(w) && !budgetState.editing;
            opt.textContent = 'Week ' + w + (used.includes(w) && !budgetState.editing ? ' (บันทึกแล้ว)' : '');
        });
        if (!budgetState.editing) {
            const first = Array.from(sel.options).find(o => !o.disabled);
            if (first) sel.value = first.value;
        }
    }

    function budgetShowToast(msg) {
        const t = document.getElementById('budgetToast');
        t.textContent = msg; t.style.display = '';
        setTimeout(() => { t.style.display = 'none'; }, 3000);
    }

    window.budgetSave = async function() {
        const btn = document.getElementById('budgetSaveBtn');
        btn.disabled = true;
        try {
            const res = await fetch('{{ route("report.save-budget") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({
                    year: budgetYear, month: budgetMonth,
                    week: document.getElementById('budgetWeek').value,
                    budget_marketing_online: document.getElementById('budgetOnline').value || 0,
                    budget_marketing_offline: document.getElementById('budgetOffline').value || 0,
                })
            });
            const data = await res.json();
            budgetState.budgets = data.budgets;
            budgetState.editing = false;
            renderBudgetTable();
            budgetCancel();
            budgetShowToast('บันทึก Budget สำเร็จ');
        } catch (e) { alert('เกิดข้อผิดพลาด: ' + e.message); }
        btn.disabled = false;
    };

    window.budgetDelete = async function(id, week) {
        if (!confirm('ลบ Budget สัปดาห์ที่ ' + week + '?')) return;
        try {
            const res = await fetch('/report/budget/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            const data = await res.json();
            budgetState.budgets = data.budgets;
            renderBudgetTable();
            budgetShowToast('ลบ Budget สำเร็จ');
        } catch (e) { alert('เกิดข้อผิดพลาด: ' + e.message); }
    };

    window.budgetEdit = function(week, online, offline) {
        budgetState.editing = true;
        const sel = document.getElementById('budgetWeek');
        Array.from(sel.options).forEach(opt => { opt.disabled = false; opt.textContent = 'Week ' + opt.value; });
        sel.value = week;
        document.getElementById('budgetOnline').value = online;
        document.getElementById('budgetOffline').value = offline;
        document.getElementById('budgetFormTitle').innerHTML = '<i class="bi bi-pencil" style="color: var(--primary);"></i> แก้ไข Budget สัปดาห์ที่ ' + week;
        document.getElementById('budgetCancelBtn').style.display = '';
    };

    window.budgetCancel = function() {
        budgetState.editing = false;
        document.getElementById('budgetOnline').value = '';
        document.getElementById('budgetOffline').value = '';
        document.getElementById('budgetFormTitle').innerHTML = '<i class="bi bi-plus-circle" style="color: var(--primary);"></i> เพิ่ม Budget รายสัปดาห์';
        document.getElementById('budgetCancelBtn').style.display = 'none';
        budgetUpdateWeekOpts();
    };

    // Initial render
    renderBudgetTable();
})();
</script>
@endsection
