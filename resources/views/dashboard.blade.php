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
    .kpi-card-units { font-size: 2.5rem; font-weight: 800; color: var(--text-dark); line-height: 1.1; }
    .kpi-card-units small { font-size: 0.85rem; font-weight: 600; color: var(--text-light); margin-left: 4px; }
    .kpi-card-value { font-size: 1rem; font-weight: 600; color: var(--text-mid); line-height: 1.3; margin-top: 0.25rem; }
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
    @media (max-width: 768px) {
        .s-card-hdr { flex-wrap: wrap; gap: 0.4rem; }
    }

    /* Toggle pills */
    .pill-toggle { display: inline-flex; gap: 2px; background: rgba(0,0,0,0.04); border-radius: 999px; padding: 2px; }
    .pill-toggle a, .pill-toggle button {
        padding: 4px 14px; border-radius: 999px; font-size: 0.72rem; font-weight: 700;
        text-decoration: none; border: none; cursor: pointer; color: var(--text-mid); background: transparent;
        transition: all 0.15s;
    }
    .pill-toggle .active { background: var(--primary); color: #fff; }

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

    /* Questionnaire banner */
    .questionnaire-banner {
        display: flex; align-items: center; gap: 1.25rem;
        background: linear-gradient(135deg, #f59e0b, #d97706, #b45309);
        border-radius: var(--radius); padding: 1.25rem 1.5rem; margin-bottom: 1.5rem;
        text-decoration: none; color: #fff; transition: all 0.25s;
        position: relative; overflow: hidden;
        box-shadow: 0 4px 15px rgba(245,158,11,0.3);
    }
    .questionnaire-banner::before {
        content: ''; position: absolute; top: -50%; right: -20%; width: 200px; height: 200px;
        background: rgba(255,255,255,0.08); border-radius: 50%;
    }
    .questionnaire-banner::after {
        content: ''; position: absolute; bottom: -60%; left: 10%; width: 150px; height: 150px;
        background: rgba(255,255,255,0.05); border-radius: 50%;
    }
    .questionnaire-banner:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(245,158,11,0.4); color: #fff; }
    .questionnaire-banner > * { position: relative; z-index: 1; }
    .questionnaire-banner-icon {
        width: 52px; height: 52px; border-radius: 14px; background: rgba(255,255,255,.22);
        display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;
        backdrop-filter: blur(4px);
    }
    .questionnaire-banner-text { flex: 1; min-width: 0; }
    .questionnaire-banner-title { font-weight: 800; font-size: 1.05rem; letter-spacing: 0.01em; }
    .questionnaire-banner-desc { font-size: 0.8rem; opacity: 0.9; margin-top: 3px; }
    .questionnaire-banner-btn {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,.22); backdrop-filter: blur(4px);
        border-radius: 999px; padding: 8px 18px;
        font-size: 0.78rem; font-weight: 700; white-space: nowrap; flex-shrink: 0;
        transition: background 0.2s;
    }
    .questionnaire-banner:hover .questionnaire-banner-btn { background: rgba(255,255,255,.32); }
</style>
@endsection

@php
    $monthLabel = \Carbon\Carbon::create($currentYear, $currentMonth)->format('M Y');
@endphp

@section('content')
    <div class="page-header">
        <h3>Overview</h3>
    </div>

    {{-- ═══ Questionnaire Banner ═══ --}}
    <a href="/questionnaire" class="questionnaire-banner">
        <div class="questionnaire-banner-icon">
            <i class="bi bi-clipboard2-check"></i>
        </div>
        <div class="questionnaire-banner-text">
            <div class="questionnaire-banner-title">Customer Questionnaire</div>
            <div class="questionnaire-banner-desc">Collect feedback from your customers — create and send questionnaires anytime</div>
        </div>
        <div class="questionnaire-banner-btn">
            Open <i class="bi bi-arrow-right"></i>
        </div>
    </a>

    {{-- ═══ SECTION 1: KPI Cards ═══ --}}
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-card-title">Transferred ({{ $monthLabel }})</div>
            <div class="kpi-card-units">{{ number_format($kpis->transferred->units) }} <small>units</small></div>
            <div class="kpi-card-value">฿{{ number_format($kpis->transferred->value, 0) }}</div>
            <a href="{{ route('buy-sale.index', ['status' => 'transferred']) }}" class="kpi-card-link">
                View details <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="kpi-card">
            <div class="kpi-card-title">Contract</div>
            <div class="kpi-card-units">{{ number_format($kpis->contract->units) }} <small>units</small></div>
            <div class="kpi-card-value">฿{{ number_format($kpis->contract->value, 0) }}</div>
            <a href="{{ route('buy-sale.index', ['status' => 'contract']) }}" class="kpi-card-link">
                View details <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="kpi-card">
            <div class="kpi-card-title">Reserved</div>
            <div class="kpi-card-units">{{ number_format($kpis->reserved->units) }} <small>units</small></div>
            <div class="kpi-card-value">฿{{ number_format($kpis->reserved->value, 0) }}</div>
            <a href="{{ route('buy-sale.index', ['status' => 'reserved']) }}" class="kpi-card-link">
                View details <i class="bi bi-arrow-right"></i>
            </a>
        </div>
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

    {{-- ═══ SECTION 3b: Unit Count by Status (bar chart) ═══ --}}
    <div class="s-card">
        <div class="s-card-hdr">
            <span class="s-card-hdr-left"><i class="bi bi-bar-chart"></i> Unit Count by Status</span>
            <span style="font-size: 0.82rem; font-weight: 700; color: var(--text-mid);">{{ $yearlyChart->year }}</span>
        </div>
        <div class="s-card-body">
            <div class="chart-wrap">
                <canvas id="unitCountChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ═══ SECTION 4: Top 5 Sale Performance ═══ --}}
    <div class="s-card mb-3">
        <div class="s-card-hdr">
            <span class="s-card-hdr-left"><i class="bi bi-trophy"></i> Top 5</span>
            <div class="pill-toggle" id="top5Toggle">
                <button class="active" data-tab="transferred">Transferred</button>
                <button data-tab="units">Units</button>
            </div>
        </div>
        <div class="s-card-body">
            {{-- Tab: Transferred --}}
            <div id="top5-transferred">
                <div class="rank-month-label mb-2">{{ $monthLabel }}</div>
                @if($top5Transferred->count())
                    @foreach($top5Transferred as $i => $person)
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

            {{-- Tab: Units --}}
            <div id="top5-units" style="display:none;">
                <div class="rank-month-label mb-2">Transferred (All time)</div>
                @if($top5Units->count())
                    @foreach($top5Units as $i => $person)
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
                            <div class="rank-val">{{ number_format($person->total_units) }} <small style="font-size:0.72rem;color:var(--text-light);">units</small></div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state"><i class="bi bi-inbox"></i> No data</div>
                @endif
            </div>
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

    const labels = @json($yearlyChart->labels);

    // ── Yearly Sales Value (line chart — 3 statuses) ──
    new Chart(document.getElementById('yearlyChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Transferred',
                    data: @json($yearlyChart->transferred),
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(42,139,146,0.08)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointBackgroundColor: '#ef4444',
                    pointHoverRadius: 5,
                },
                {
                    label: 'Contract',
                    data: @json($yearlyChart->contract),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(245,158,11,0.08)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointBackgroundColor: '#3b82f6',
                    pointHoverRadius: 5,
                },
                {
                    label: 'Reserved',
                    data: @json($yearlyChart->reserved),
                    borderColor: '#facc15',
                    backgroundColor: 'rgba(99,102,241,0.08)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointBackgroundColor: '#facc15',
                    pointHoverRadius: 5,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 11, weight: '600' } } },
                tooltip: {
                    backgroundColor: '#1a2e35', padding: 10, cornerRadius: 8,
                    callbacks: { label: ctx => ctx.dataset.label + ': ฿' + ctx.parsed.y.toLocaleString() }
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

    // ── Unit Count by Status (bar chart) ──
    new Chart(document.getElementById('unitCountChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Transferred',
                    data: @json($yearlyChart->countTransferred),
                    backgroundColor: '#ef4444',
                    borderRadius: 4,
                },
                {
                    label: 'Contract',
                    data: @json($yearlyChart->countContract),
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                },
                {
                    label: 'Reserved',
                    data: @json($yearlyChart->countReserved),
                    backgroundColor: '#facc15',
                    borderRadius: 4,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 11, weight: '600' } } },
                tooltip: {
                    backgroundColor: '#1a2e35', padding: 10, cornerRadius: 8,
                    callbacks: { label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y + ' units' }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { stepSize: 1, precision: 0 }
                },
                x: { grid: { display: false } }
            }
        }
    });
})();

// ── Top 5 tab toggle ──
document.querySelectorAll('#top5Toggle button').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('#top5Toggle button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const tab = btn.dataset.tab;
        document.getElementById('top5-transferred').style.display = tab === 'transferred' ? '' : 'none';
        document.getElementById('top5-units').style.display = tab === 'units' ? '' : 'none';
    });
});
</script>
@endsection
