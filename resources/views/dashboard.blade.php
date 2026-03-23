@extends('layouts.app')

@section('title', 'Overview')

@section('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .page-header h3 { font-weight: 700; margin: 0; }

    /* KPI row */
    .kpi-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    @media (max-width: 767px) { .kpi-row { grid-template-columns: 1fr; } }
    .kpi-card {
        background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
        padding: 1.5rem; transition: all 0.2s;
    }
    .kpi-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .kpi-card-title { font-size: 0.75rem; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.5rem; }
    .kpi-card-value { font-size: 1.65rem; font-weight: 800; color: var(--text-dark); line-height: 1.1; }
    .kpi-card-link { display: inline-flex; align-items: center; gap: 4px; margin-top: 0.65rem; font-size: 0.78rem; font-weight: 600; color: var(--primary); text-decoration: none; }
    .kpi-card-link:hover { text-decoration: underline; }

    /* Section card */
    .s-card {
        background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
        overflow: hidden; margin-bottom: 1.25rem;
    }
    .s-card-hdr {
        display: flex; align-items: center; justify-content: space-between;
        padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border);
        background: var(--bg, #faf8f5);
    }
    .s-card-hdr-left { display: flex; align-items: center; gap: 0.5rem; font-weight: 700; font-size: 0.88rem; color: var(--text-dark); }
    .s-card-hdr-left i { color: var(--primary); }
    .s-card-body { padding: 1.25rem; }

    /* Toggle pills */
    .pill-toggle { display: inline-flex; gap: 2px; background: rgba(0,0,0,0.04); border-radius: 999px; padding: 2px; }
    .pill-toggle a, .pill-toggle button {
        padding: 4px 14px; border-radius: 999px; font-size: 0.72rem; font-weight: 700;
        text-decoration: none; border: none; cursor: pointer; color: var(--text-mid); background: transparent;
        transition: all 0.15s;
    }
    .pill-toggle .active { background: var(--primary); color: #fff; }

    /* Activity inline row */
    .act-inline {
        display: flex; flex-wrap: wrap; gap: 0.75rem;
        background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
        padding: 1rem 1.25rem; margin-bottom: 1.5rem;
    }
    .act-chip {
        display: flex; align-items: center; gap: 0.5rem;
        padding: 0.45rem 0.85rem; border-radius: var(--radius-sm);
        border: 1px solid var(--border); background: var(--surface);
        font-size: 0.8rem; transition: all 0.15s; flex: 1; min-width: 130px;
    }
    .act-chip:hover { box-shadow: var(--shadow-sm); }
    .act-chip-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .act-chip-label { font-weight: 600; color: var(--text-mid); }
    .act-chip-count { font-weight: 800; color: var(--text-dark); margin-left: auto; }
    @media (max-width: 767px) { .act-chip { min-width: calc(50% - 0.5rem); } }

    /* Chart */
    .chart-wrap { position: relative; height: 300px; }
    .chart-headline { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin-bottom: 1rem; }
    .chart-headline small { font-size: 0.78rem; font-weight: 600; color: var(--text-light); margin-left: 6px; }

    /* Ranked list */
    .rank-item { display: flex; align-items: center; gap: 0.85rem; padding: 0.7rem 0; }
    .rank-item + .rank-item { border-top: 1px solid rgba(0,0,0,0.04); }
    .rank-avatar {
        width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 0.72rem; font-weight: 800; flex-shrink: 0;
    }
    .rank-avatar.r1 { background: #fef3c7; color: #b45309; }
    .rank-avatar.r2 { background: #f1f5f9; color: #475569; }
    .rank-avatar.r3 { background: #fef2f2; color: #b91c1c; }
    .rank-avatar.r4 { background: rgba(42,139,146,0.08); color: var(--primary); }
    .rank-info { flex: 1; min-width: 0; }
    .rank-name { font-weight: 700; font-size: 0.84rem; color: var(--text-dark); }
    .rank-team { font-size: 0.72rem; color: var(--text-light); }
    .rank-val { font-weight: 800; font-size: 0.88rem; color: var(--text-dark); white-space: nowrap; }
    .rank-month-label { font-size: 0.72rem; font-weight: 600; color: var(--text-light); }

    /* Appointment list */
    .appt-item { display: flex; align-items: flex-start; gap: 0.85rem; padding: 0.75rem 0; }
    .appt-item + .appt-item { border-top: 1px solid rgba(0,0,0,0.04); }
    .appt-date-block {
        width: 48px; padding: 6px 0; border-radius: var(--radius-sm); text-align: center; flex-shrink: 0;
        background: rgba(124,58,237,0.08);
    }
    .appt-day { font-size: 1.1rem; font-weight: 800; color: #7c3aed; line-height: 1; }
    .appt-month { font-size: 0.62rem; font-weight: 700; color: #7c3aed; text-transform: uppercase; }
    .appt-info { flex: 1; min-width: 0; }
    .appt-sale { font-weight: 700; font-size: 0.84rem; color: var(--text-dark); }
    .appt-unit { font-size: 0.78rem; color: var(--text-mid); }
    .appt-person { font-size: 0.72rem; color: var(--text-light); margin-top: 2px; }


    .empty-state { text-align: center; padding: 2rem; color: var(--text-light); font-size: 0.85rem; }
    .empty-state i { font-size: 1.5rem; display: block; margin-bottom: 0.5rem; }
</style>
@endsection

@php
    $statusMeta = [
        'available'   => ['label' => 'Available',   'color' => '#12b76a'],
        'appointment' => ['label' => 'Appointment', 'color' => '#7c3aed'],
        'reserved'    => ['label' => 'Reserved',    'color' => '#0ba5ec'],
        'contract'    => ['label' => 'Contract',    'color' => '#f79009'],
        'installment' => ['label' => 'Installment', 'color' => '#2A8B92'],
        'transferred' => ['label' => 'Transferred', 'color' => '#101828'],
    ];
    $monthLabel = \Carbon\Carbon::create($currentYear, $currentMonth)->format('M Y');
@endphp

@section('content')
    <div class="page-header">
        <h3>Overview</h3>
    </div>

    {{-- ═══ SECTION 1: KPI Cards ═══ --}}
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-card-title">Transferred ({{ $monthLabel }})</div>
            <div class="kpi-card-value">฿{{ number_format($kpis->transferred, 0) }}</div>
            <a href="{{ route('buy-sale.index', ['status' => 'transferred']) }}" class="kpi-card-link">
                View details <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="kpi-card">
            <div class="kpi-card-title">Reserved</div>
            <div class="kpi-card-value">฿{{ number_format($kpis->reserved, 0) }}</div>
            <a href="{{ route('buy-sale.index', ['status' => 'reserved']) }}" class="kpi-card-link">
                View details <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="kpi-card">
            <div class="kpi-card-title">Contract</div>
            <div class="kpi-card-value">฿{{ number_format($kpis->contract, 0) }}</div>
            <a href="{{ route('buy-sale.index', ['status' => 'contract']) }}" class="kpi-card-link">
                View details <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    {{-- ═══ SECTION 2: Activities (inline row) ═══ --}}
    <div class="act-inline">
        @foreach($statusMeta as $key => $meta)
            <div class="act-chip">
                <span class="act-chip-dot" style="background: {{ $meta['color'] }};"></span>
                <span class="act-chip-label">{{ $meta['label'] }}</span>
                <span class="act-chip-count">{{ number_format($activitySummary[$key] ?? 0) }}</span>
            </div>
        @endforeach
    </div>

    {{-- ═══ SECTION 3: Yearly Sales Chart ═══ --}}
    <div class="s-card">
        <div class="s-card-hdr">
            <span class="s-card-hdr-left"><i class="bi bi-graph-up"></i> Total Yearly Sales</span>
            <span style="font-size: 0.82rem; font-weight: 700; color: var(--text-mid);">{{ $yearlyChart->year }}</span>
        </div>
        <div class="s-card-body">
            <div class="chart-headline">
                ฿{{ number_format($yearlyChart->total, 0) }}
                <small>Total {{ $yearlyChart->year }}</small>
            </div>
            <div class="chart-wrap">
                <canvas id="yearlyChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ═══ SECTION 4: Top 5 Sale Performance ═══ --}}
    <div class="s-card mb-3">
        <div class="s-card-hdr">
            <span class="s-card-hdr-left"><i class="bi bi-trophy"></i> Top 5</span>
            <div class="pill-toggle">
                <a href="{{ route('dashboard', ['view' => 'transferred']) }}" class="{{ $view === 'transferred' ? 'active' : '' }}">Transferred</a>
                <a href="{{ route('dashboard', ['view' => 'active']) }}" class="{{ $view === 'active' ? 'active' : '' }}">Active</a>
            </div>
        </div>
        <div class="s-card-body">
            <div class="rank-month-label mb-2">{{ $view === 'active' ? 'Active Pipeline' : $monthLabel }}</div>
            @if($top5->count())
                @foreach($top5 as $i => $person)
                    @php
                        $initials = collect(explode(' ', $person->name))->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('');
                        $rankClass = $i < 3 ? 'r' . ($i + 1) : 'r4';
                    @endphp
                    <div class="rank-item">
                        <div class="rank-avatar {{ $rankClass }}">{{ $initials }}</div>
                        <div class="rank-info">
                            <div class="rank-name">{{ $person->name }}</div>
                            <div class="rank-team">{{ $person->team_name ?? '-' }}</div>
                        </div>
                        <div class="rank-val">฿{{ number_format($person->total_value, 0) }}</div>
                    </div>
                @endforeach
            @else
                <div class="empty-state"><i class="bi bi-inbox"></i> No data</div>
            @endif
        </div>
    </div>

    {{-- ═══ SECTION 5: Upcoming Appointments ═══ --}}
    <div class="s-card mb-3">
        <div class="s-card-hdr">
            <span class="s-card-hdr-left"><i class="bi bi-calendar-event"></i> Upcoming Appointments</span>
        </div>
        <div class="s-card-body">
            @if($appointments->count())
                @foreach($appointments as $appt)
                    @php $d = \Carbon\Carbon::parse($appt->appointment_date); @endphp
                    <div class="appt-item">
                        <div class="appt-date-block">
                            <div class="appt-day">{{ $d->format('d') }}</div>
                            <div class="appt-month">{{ $d->format('M') }}</div>
                        </div>
                        <div class="appt-info">
                            <div class="appt-sale">{{ $appt->sale_number }}</div>
                            <div class="appt-unit">{{ $appt->unit_code }}
                                @if($appt->appointment_time)
                                    &middot; {{ \Carbon\Carbon::parse($appt->appointment_time)->format('H:i') }}
                                @endif
                            </div>
                            <div class="appt-person">{{ $appt->user_name ?? '-' }}</div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state"><i class="bi bi-calendar-x"></i> No upcoming appointments</div>
            @endif
        </div>
    </div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function() {
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#6b8c93';

    const ctx = document.getElementById('yearlyChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($yearlyChart->labels),
            datasets: [{
                label: 'Sale Value',
                data: @json($yearlyChart->values),
                borderColor: '#2A8B92',
                backgroundColor: 'rgba(42,139,146,0.08)',
                fill: true,
                tension: 0.35,
                borderWidth: 2.5,
                pointRadius: 3,
                pointBackgroundColor: '#2A8B92',
                pointHoverRadius: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a2e35', padding: 10, cornerRadius: 8,
                    callbacks: {
                        label: ctx => '฿' + ctx.parsed.y.toLocaleString()
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { callback: v => '฿' + (v >= 1e6 ? (v / 1e6).toFixed(1) + 'M' : v.toLocaleString()) }
                },
                x: { grid: { display: false } }
            }
        }
    });
})();

</script>
@endsection
