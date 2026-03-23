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
    .donut-canvas-wrap { flex-shrink: 0; width: 180px; height: 180px; position: relative; }
    .donut-canvas-wrap canvas { width: 100% !important; height: 100% !important; }
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
    </div>

    <div class="view-toggle-wrapper">
        <div class="view-toggle" id="viewToggle">
            <button type="button" data-view="transferred" class="{{ $view === 'transferred' ? 'active' : '' }}">Transferred</button>
            <button type="button" data-view="active" class="{{ $view === 'active' ? 'active' : '' }}">Active</button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        {{-- ── Sale Value by Team (donut) ────────────────────── --}}
        <div class="col-lg-6">
            <div class="section-card mb-0">
                <div class="section-card-header">
                    <span><i class="bi bi-people"></i> Sale Value by Team</span>
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
                            </div>
                            <div class="donut-list">
                                @foreach($teamChart as $i => $row)
                                    <div class="donut-item">
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
                            </div>
                            <div class="donut-list">
                                @foreach($saleChart->take(8) as $i => $row)
                                    <div class="donut-item">
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

    {{-- ── Production Line Chart ─────────────────────────── --}}
    <div class="section-card">
        <div class="section-card-header">
            <i class="bi bi-graph-up"></i> Production ({{ $year }})
        </div>
        <div class="section-card-body">
            <div class="chart-container">
                <canvas id="productionChart"></canvas>
            </div>
        </div>
    </div>
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

    // ── Team Donut ──
    @if($teamChart->count())
    new Chart(document.getElementById('teamDonut'), {
        type: 'doughnut',
        data: {
            labels: @json($teamChart->pluck('team_name')->toArray()),
            datasets: [{
                data: @json($teamChart->pluck('total_value')->toArray()),
                backgroundColor: colors.slice(0, {{ $teamChart->count() }}),
                borderWidth: 0, hoverOffset: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true, cutout: '65%',
            plugins: { legend: { display: false }, tooltip: {
                backgroundColor: '#1a2e35', padding: 10, cornerRadius: 8,
                callbacks: { label: ctx => '฿' + Number(ctx.raw).toLocaleString() }
            }}
        }
    });
    @endif

    // ── Sale Donut ──
    @if($saleChart->count())
    new Chart(document.getElementById('saleDonut'), {
        type: 'doughnut',
        data: {
            labels: @json($saleChart->take(8)->pluck('name')->toArray()),
            datasets: [{
                data: @json($saleChart->take(8)->pluck('total_value')->toArray()),
                backgroundColor: colors.slice(0, {{ min($saleChart->count(), 8) }}),
                borderWidth: 0, hoverOffset: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true, cutout: '65%',
            plugins: { legend: { display: false }, tooltip: {
                backgroundColor: '#1a2e35', padding: 10, cornerRadius: 8,
                callbacks: { label: ctx => '฿' + Number(ctx.raw).toLocaleString() }
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

    // ── Production Area Chart ──
    new Chart(document.getElementById('productionChart'), {
        type: 'line',
        data: {
            labels: @json($productionChart->labels),
            datasets: [{
                label: 'Transferred Value',
                data: @json($productionChart->values),
                borderColor: '#2A8B92',
                backgroundColor: 'rgba(42, 139, 146, 0.1)',
                fill: true, tension: 0.4, borderWidth: 2,
                pointBackgroundColor: '#2A8B92', pointRadius: 3, pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a2e35', padding: 10, cornerRadius: 8,
                    callbacks: { label: ctx => '฿' + Number(ctx.raw).toLocaleString() }
                }
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { callback: v => '฿' + (v >= 1e6 ? (v/1e6).toFixed(1) + 'M' : v >= 1e3 ? (v/1e3).toFixed(0) + 'K' : v) }
                }
            }
        }
    });
})();
</script>
@endsection
