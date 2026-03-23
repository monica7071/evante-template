@extends('layouts.app')

@section('title', 'Finance')

@section('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem; }
    .page-header h3 { font-weight: 700; margin: 0; }

    /* KPI mini cards */
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    @media (max-width: 1199px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .kpi-grid { grid-template-columns: 1fr; } }
    .kpi-mini {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius); padding: 1rem 1.25rem;
    }
    .kpi-mini-label { font-size: 0.72rem; font-weight: 600; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.03em; }
    .kpi-mini-value { font-size: 1.25rem; font-weight: 800; color: var(--text-dark); margin-top: 4px; line-height: 1.2; }

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

    /* Year nav */
    .year-nav {
        display: flex; align-items: center; gap: 0.75rem;
        margin-left: auto; font-weight: 400;
    }
    .year-nav a {
        width: 28px; height: 28px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid var(--border); color: var(--text-mid);
        text-decoration: none; font-size: 0.8rem; transition: all 0.15s;
    }
    .year-nav a:hover { border-color: var(--primary); color: var(--primary); }
    .year-nav span { font-weight: 800; font-size: 0.92rem; color: var(--text-dark); }

    /* Quarter selector */
    .quarter-tabs {
        display: flex; gap: 0.35rem; margin-bottom: 1.25rem;
    }
    .quarter-tab {
        flex: 1; text-align: center; padding: 0.55rem 0;
        border: 1px solid var(--border); border-radius: var(--radius-sm);
        font-size: 0.78rem; font-weight: 700; color: var(--text-mid);
        text-decoration: none; transition: all 0.15s; background: var(--surface);
    }
    .quarter-tab:hover { border-color: var(--primary); color: var(--primary); }
    .quarter-tab.active { background: var(--primary); color: #fff; border-color: var(--primary); }

    /* Month cards */
    .month-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.25rem; }
    @media (max-width: 767px) { .month-cards { grid-template-columns: 1fr; } }
    .month-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius); padding: 1.25rem; text-align: center;
    }
    .month-card-label { font-size: 0.82rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem; }
    .month-card-value { font-size: 1.3rem; font-weight: 800; color: var(--primary); line-height: 1.2; }
    .month-card-deals { font-size: 0.72rem; font-weight: 600; color: var(--text-light); margin-top: 4px; }

    /* Chart */
    .chart-container { position: relative; height: 280px; }

    /* Accordion */
    .person-accordion { border-top: 1px solid var(--border); }
    .person-header {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.85rem 1.25rem; cursor: pointer; transition: background 0.1s;
        border-bottom: 1px solid rgba(0,0,0,0.04);
    }
    .person-header:hover { background: rgba(42,139,146,0.03); }
    .person-chevron { font-size: 0.75rem; color: var(--text-light); transition: transform 0.2s; flex-shrink: 0; }
    .person-header.open .person-chevron { transform: rotate(90deg); }
    .person-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.72rem; font-weight: 700; color: #fff; flex-shrink: 0;
    }
    .person-info { flex: 1; min-width: 0; }
    .person-name { font-weight: 700; font-size: 0.84rem; color: var(--text-dark); }
    .person-team { font-size: 0.72rem; color: var(--text-light); }
    .person-stats { display: flex; gap: 1rem; flex-shrink: 0; text-align: right; }
    .person-stat-value { font-weight: 800; font-size: 0.88rem; color: var(--text-dark); }
    .person-stat-label { font-size: 0.65rem; font-weight: 600; color: var(--text-light); text-transform: uppercase; }
    .person-deals {
        display: none; background: rgba(0,0,0,0.015);
        border-bottom: 1px solid var(--border);
    }
    .person-deals.open { display: block; }
    .person-deal-row {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.55rem 1.25rem 0.55rem 4.5rem;
        font-size: 0.8rem; border-bottom: 1px solid rgba(0,0,0,0.03);
    }
    .person-deal-row:last-child { border-bottom: none; }
    .deal-sale { font-weight: 700; color: var(--text-dark); min-width: 90px; }
    .deal-unit { color: var(--text-mid); min-width: 80px; }
    .deal-date { font-size: 0.72rem; color: var(--text-light); }
    .deal-value { margin-left: auto; font-weight: 700; color: var(--text-dark); }

    .empty-state {
        text-align: center; padding: 2rem 1rem; color: var(--text-light); font-size: 0.85rem;
    }
    .empty-state i { font-size: 1.75rem; display: block; margin-bottom: 0.5rem; }
</style>
@endsection

@section('content')
    <div class="page-header">
        <h3>Finance</h3>
    </div>

    {{-- ── 4 KPI Mini-Cards ──────────────────────────────────────── --}}
    <div class="kpi-grid">
        <div class="kpi-mini">
            <div class="kpi-mini-label">Reservation Deposits</div>
            <div class="kpi-mini-value">฿{{ number_format($kpis->reservation_total, 0) }}</div>
        </div>
        <div class="kpi-mini">
            <div class="kpi-mini-label">Contract Payments</div>
            <div class="kpi-mini-value">฿{{ number_format($kpis->contract_payment_total, 0) }}</div>
        </div>
        <div class="kpi-mini">
            <div class="kpi-mini-label">Transfer Amount</div>
            <div class="kpi-mini-value">฿{{ number_format($kpis->transfer_amount_total, 0) }}</div>
        </div>
        <div class="kpi-mini">
            <div class="kpi-mini-label">Fees Collected</div>
            <div class="kpi-mini-value">฿{{ number_format($kpis->fees_total, 0) }}</div>
        </div>
    </div>

    {{-- ── Yearly Area Chart ─────────────────────────────────────── --}}
    <div class="section-card">
        <div class="section-card-header">
            <i class="bi bi-graph-up"></i> Yearly Transferred Value
            <div class="year-nav">
                <a href="{{ route('finance.index', ['year' => $year - 1, 'quarter' => $quarter]) }}"><i class="bi bi-chevron-left"></i></a>
                <span>{{ $year }}</span>
                <a href="{{ route('finance.index', ['year' => $year + 1, 'quarter' => $quarter]) }}"><i class="bi bi-chevron-right"></i></a>
            </div>
        </div>
        <div class="section-card-body">
            @php
                $qStartIdx = ($quarter - 1) * 3;
                $quarterTotal = array_sum(array_slice($yearlyChart->values, $qStartIdx, 3));
            @endphp
            <div style="text-align: center; margin-bottom: 0.75rem;">
                <span style="font-size: 1.5rem; font-weight: 800; color: var(--primary);">฿{{ number_format($quarterTotal, 0) }}</span>
                <span style="font-size: 0.78rem; color: var(--text-light); margin-left: 0.5rem;">Q{{ $quarter }} Total</span>
                <span style="font-size: 0.78rem; color: var(--text-light); margin-left: 1rem;">|</span>
                <span style="font-size: 1rem; font-weight: 700; color: var(--text-mid); margin-left: 0.75rem;">฿{{ number_format($yearlyChart->total, 0) }}</span>
                <span style="font-size: 0.72rem; color: var(--text-light); margin-left: 0.35rem;">Year {{ $year }}</span>
            </div>
            <div class="chart-container">
                <canvas id="yearlyChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Quarter Selector + Month Cards ────────────────────────── --}}
    <div class="quarter-tabs">
        @for($q = 1; $q <= 4; $q++)
            <a href="{{ route('finance.index', ['year' => $year, 'quarter' => $q]) }}"
               class="quarter-tab {{ $quarter == $q ? 'active' : '' }}">Q{{ $q }}</a>
        @endfor
    </div>

    <div class="month-cards">
        @foreach($quarterMonths as $mc)
            <div class="month-card">
                <div class="month-card-label">{{ $mc->label }} {{ $year }}</div>
                <div class="month-card-value">฿{{ number_format($mc->total_value, 0) }}</div>
                <div class="month-card-deals">{{ $mc->deal_count }} deals transferred</div>
            </div>
        @endforeach
    </div>

    {{-- ── Per Sale Person Accordion ─────────────────────────────── --}}
    <div class="section-card">
        <div class="section-card-header">
            <i class="bi bi-person-badge"></i> Sale Person Breakdown ({{ $year }})
        </div>
        @if($personData->count())
            @php $avatarColors = ['#2A8B92', '#7c3aed', '#f79009', '#0ba5ec', '#12b76a', '#dc3545']; @endphp
            <div class="person-accordion">
                @foreach($personData as $i => $person)
                    @php
                        $initials = collect(explode(' ', $person->name))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->join('');
                        $deals = $personDeals[$person->user_id] ?? [];
                    @endphp
                    <div class="person-header" onclick="toggleAccordion(this)">
                        <i class="bi bi-chevron-right person-chevron"></i>
                        <span class="person-avatar" style="background: {{ $avatarColors[$i % count($avatarColors)] }};">{{ $initials }}</span>
                        <div class="person-info">
                            <div class="person-name">{{ $person->name }}</div>
                            <div class="person-team">{{ $person->team_name ?? '—' }}</div>
                        </div>
                        <div class="person-stats">
                            <div>
                                <div class="person-stat-value">{{ $person->deal_count }}</div>
                                <div class="person-stat-label">Deals</div>
                            </div>
                            <div>
                                <div class="person-stat-value">฿{{ number_format($person->total_value, 0) }}</div>
                                <div class="person-stat-label">Total Value</div>
                            </div>
                        </div>
                    </div>
                    <div class="person-deals">
                        @foreach($deals as $deal)
                            <div class="person-deal-row">
                                <span class="deal-sale">{{ $deal->sale_number }}</span>
                                <span class="deal-unit">{{ $deal->unit_code }}</span>
                                <span class="deal-date">{{ \Carbon\Carbon::parse($deal->created_at)->format('d M Y') }}</span>
                                <span class="deal-value">฿{{ number_format($deal->price_per_room, 0) }}</span>
                            </div>
                        @endforeach
                        @if(empty($deals))
                            <div class="person-deal-row" style="color: var(--text-light); justify-content: center;">No deal details</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                No transferred deals for {{ $year }}.
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function() {
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#6b8c93';

    // ── Quarter Line Chart (only selected quarter's 3 months) ──
    const qLabels = @json($quarterChartLabels);
    const qValues = @json($quarterChartValues);
    const hasData = qValues.some(v => v > 0);

    if (hasData) {
        new Chart(document.getElementById('yearlyChart'), {
            type: 'line',
            data: {
                labels: qLabels,
                datasets: [{
                    label: 'Transferred Value',
                    data: qValues,
                    borderColor: '#2A8B92',
                    backgroundColor: 'rgba(42, 139, 146, 0.1)',
                    fill: true, tension: 0.4, borderWidth: 2.5,
                    pointBackgroundColor: '#2A8B92', pointRadius: 5, pointHoverRadius: 7,
                    pointBorderColor: '#fff', pointBorderWidth: 2,
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
    } else {
        document.getElementById('yearlyChart').parentElement.innerHTML =
            '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-light);font-size:0.85rem;">' +
            '<div style="text-align:center;"><i class="bi bi-graph-up" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;"></i>No transferred data for Q{{ $quarter }}</div></div>';
    }

    // Accordion toggle
    window.toggleAccordion = function(header) {
        header.classList.toggle('open');
        const deals = header.nextElementSibling;
        deals.classList.toggle('open');
    };
})();
</script>
@endsection
