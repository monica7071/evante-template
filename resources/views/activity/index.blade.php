@extends('layouts.app')

@section('title', 'Activity')

@section('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem; }
    .page-header h3 { font-weight: 700; margin: 0; }

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

    /* Month nav */
    .month-nav {
        display: flex; align-items: center; gap: 0.75rem;
        margin-left: auto; font-weight: 400;
    }
    .month-nav a {
        width: 28px; height: 28px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid var(--border); color: var(--text-mid);
        text-decoration: none; font-size: 0.8rem; transition: all 0.15s;
    }
    .month-nav a:hover { border-color: var(--primary); color: var(--primary); }
    .month-nav span { font-weight: 800; font-size: 0.92rem; color: var(--text-dark); }
    .month-nav .today-btn {
        width: auto; padding: 0 0.75rem; font-size: 0.75rem; font-weight: 600;
        border-radius: var(--radius-sm);
    }

    /* Calendar grid */
    .cal-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .cal-table th {
        text-align: center; font-weight: 700; font-size: 0.7rem;
        text-transform: uppercase; letter-spacing: 0.04em;
        color: var(--text-light); padding: 0.5rem 0.25rem;
        border-bottom: 1px solid var(--border);
    }
    .cal-table td {
        vertical-align: top; padding: 0.35rem; height: 85px;
        border: 1px solid rgba(0,0,0,0.04); font-size: 0.78rem;
    }
    .cal-day-num {
        font-weight: 700; font-size: 0.72rem; color: var(--text-mid);
        margin-bottom: 2px;
    }
    .cal-day-num.today {
        background: var(--primary); color: #fff;
        width: 22px; height: 22px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .cal-day-num.other-month { color: var(--text-light); opacity: 0.4; }
    .cal-event {
        display: block; font-size: 0.62rem; font-weight: 600;
        padding: 1px 4px; border-radius: 3px; margin-bottom: 1px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        line-height: 1.4;
    }
    .cal-more { font-size: 0.6rem; color: var(--text-light); font-weight: 600; padding-left: 4px; }
    .cal-empty { background: rgba(0,0,0,0.01); }

    /* Activity bars */
    .act-bar-item { margin-bottom: 0.65rem; }
    .act-bar-item:last-child { margin-bottom: 0; }
    .act-bar-top { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 3px; }
    .act-bar-label { font-size: 0.82rem; font-weight: 600; color: var(--text-dark); text-transform: capitalize; }
    .act-bar-count { font-size: 0.78rem; font-weight: 700; color: var(--text-mid); }
    .act-bar-track { height: 20px; background: rgba(0,0,0,0.04); border-radius: 4px; overflow: hidden; }
    .act-bar-fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; min-width: 2px; }

    /* Appointment cards */
    .appt-list { list-style: none; margin: 0; padding: 0; }
    .appt-item {
        display: flex; align-items: flex-start; gap: 0.75rem;
        padding: 0.75rem 0; border-bottom: 1px solid rgba(0,0,0,0.04);
    }
    .appt-item:last-child { border-bottom: none; }
    .appt-date-block {
        width: 48px; text-align: center; flex-shrink: 0;
        background: rgba(42,139,146,0.06); border-radius: var(--radius-sm);
        padding: 0.4rem 0.25rem;
    }
    .appt-date-block.today { background: rgba(124,58,237,0.1); }
    .appt-date-day { font-size: 1.15rem; font-weight: 800; line-height: 1; color: var(--text-dark); }
    .appt-date-mon { font-size: 0.62rem; font-weight: 700; color: var(--text-light); text-transform: uppercase; }
    .appt-body { flex: 1; min-width: 0; }
    .appt-title { font-weight: 700; font-size: 0.84rem; color: var(--text-dark); }
    .appt-unit { color: var(--text-mid); margin-left: 4px; font-weight: 400; }
    .appt-detail { font-size: 0.75rem; color: var(--text-light); margin-top: 2px; }
    .appt-detail i { margin-right: 3px; }
    .appt-user { font-size: 0.72rem; font-weight: 600; color: var(--text-mid); flex-shrink: 0; align-self: center; }
    .appt-today-badge {
        font-size: 0.62rem; font-weight: 700; padding: 2px 8px;
        border-radius: 999px; background: rgba(124,58,237,0.12); color: #7c3aed;
    }

    .empty-state {
        text-align: center; padding: 2rem 1rem; color: var(--text-light); font-size: 0.85rem;
    }
    .empty-state i { font-size: 1.75rem; display: block; margin-bottom: 0.5rem; }

    /* ── Mobile Calendar (iOS-style) ── */
    .mobile-cal { display: none; }

    @media (max-width: 767px) {
        .cal-table { display: none; }
        .mobile-cal { display: block; }

        .mcal-grid {
            display: grid; grid-template-columns: repeat(7, 1fr);
            text-align: center; gap: 0;
        }
        .mcal-head {
            font-size: 0.68rem; font-weight: 700; color: var(--text-light);
            text-transform: uppercase; letter-spacing: 0.03em;
            padding: 0.4rem 0;
        }
        .mcal-day {
            padding: 0.35rem 0; cursor: pointer;
            display: flex; flex-direction: column; align-items: center; gap: 3px;
            -webkit-tap-highlight-color: transparent;
        }
        .mcal-day-num {
            width: 36px; height: 36px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.88rem; font-weight: 500; color: var(--text-dark);
            transition: all 0.15s;
        }
        .mcal-day.other-month .mcal-day-num { color: var(--text-light); opacity: 0.35; }
        .mcal-day.is-today .mcal-day-num { background: var(--primary); color: #fff; font-weight: 700; }
        .mcal-day.selected .mcal-day-num { background: var(--text-dark); color: #fff; font-weight: 700; }
        .mcal-day.is-today.selected .mcal-day-num { background: var(--primary); }

        .mcal-dots {
            display: flex; gap: 3px; height: 5px; align-items: center; justify-content: center;
        }
        .mcal-dot {
            width: 5px; height: 5px; border-radius: 50%;
        }

        /* Event list below calendar */
        .mcal-events { padding: 0.5rem 0.25rem 0.25rem; }
        .mcal-events-header {
            font-size: 0.78rem; font-weight: 700; color: var(--text-dark);
            padding: 0.5rem 0.5rem 0.4rem; border-bottom: 1px solid var(--border);
        }
        .mcal-event-card {
            display: flex; align-items: center; gap: 0.65rem;
            padding: 0.7rem 0.5rem; border-bottom: 1px solid rgba(0,0,0,0.04);
            cursor: pointer; -webkit-tap-highlight-color: transparent;
        }
        .mcal-event-card:last-child { border-bottom: none; }
        .mcal-event-card:active { background: rgba(0,0,0,0.03); }
        .mcal-event-dot-lg {
            width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
        }
        .mcal-event-body { flex: 1; min-width: 0; }
        .mcal-event-title {
            font-size: 0.84rem; font-weight: 700; color: var(--text-dark);
        }
        .mcal-event-sub {
            font-size: 0.73rem; color: var(--text-light); margin-top: 1px;
        }
        .mcal-event-badge {
            font-size: 0.65rem; font-weight: 700; padding: 2px 8px;
            border-radius: 999px; text-transform: capitalize; flex-shrink: 0;
        }
        .mcal-no-events {
            text-align: center; padding: 1.5rem 1rem; color: var(--text-light); font-size: 0.82rem;
        }
    }

    /* ── Calendar Event Popup ── */
    .cal-popup-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.45); z-index: 1050;
        align-items: center; justify-content: center;
    }
    .cal-popup-overlay.active { display: flex; }
    .cal-popup {
        background: #fff; border-radius: 14px; width: 380px; max-width: 92%;
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        animation: cpopSlideUp 0.2s ease; position: relative;
        overflow: hidden;
    }
    @keyframes cpopSlideUp {
        from { transform: translateY(12px); opacity: 0; }
        to   { transform: translateY(0); opacity: 1; }
    }
    .cal-popup-close {
        position: absolute; top: 10px; right: 12px;
        background: none; border: none; font-size: 20px; color: #94a3b8;
        cursor: pointer; line-height: 1; z-index: 2;
    }
    .cal-popup-close:hover { color: #475569; }

    .cpop-header { padding: 16px 20px 12px; border-bottom: 1px solid #f1f5f9; }
    .cpop-status {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 0.82rem; font-weight: 700; padding: 4px 12px;
        border-radius: 20px; text-transform: uppercase; letter-spacing: 0.03em;
    }
    .cpop-status.appointment { background: rgba(124,58,237,0.12); color: #7c3aed; }
    .cpop-status.transferred { background: rgba(16,24,40,0.08); color: #475569; }

    .cpop-body { padding: 14px 20px; }
    .cpop-row {
        display: flex; justify-content: space-between; align-items: baseline;
        padding: 6px 0; border-bottom: 1px solid #f8fafc;
    }
    .cpop-row:last-child { border-bottom: none; }
    .cpop-row-full { flex-direction: column; gap: 2px; }
    .cpop-label { font-size: 0.75rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.03em; }
    .cpop-value { font-size: 0.88rem; font-weight: 600; color: #1e293b; }
    .cpop-row-full .cpop-value { font-weight: 400; color: #475569; font-size: 0.82rem; }

    .cpop-footer {
        padding: 12px 20px; border-top: 1px solid #f1f5f9;
        text-align: center;
    }
    .cpop-link {
        font-size: 0.82rem; font-weight: 600; color: var(--primary, #2A8B92);
        text-decoration: none;
    }
    .cpop-link:hover { text-decoration: underline; }
</style>
@endsection

@php
    $now = \Carbon\Carbon::now();
    $isCurrentMonth = ($year == $now->year && $month == $now->month);
    $monthLabel = $calendarDate->format('F Y');

    $prevMonth = $calendarDate->copy()->subMonth();
    $nextMonth = $calendarDate->copy()->addMonth();

    $statusColors = [
        'available'   => ['bg' => 'rgba(18,183,106,0.15)', 'color' => '#12b76a', 'fill' => '#12b76a'],
        'appointment' => ['bg' => 'rgba(124,58,237,0.15)', 'color' => '#7c3aed', 'fill' => '#7c3aed'],
        'reserved'    => ['bg' => 'rgba(11,165,236,0.15)', 'color' => '#0ba5ec', 'fill' => '#0ba5ec'],
        'contract'    => ['bg' => 'rgba(247,144,9,0.15)',  'color' => '#f79009', 'fill' => '#f79009'],
        'installment' => ['bg' => 'rgba(42,139,146,0.15)', 'color' => '#2A8B92', 'fill' => '#2A8B92'],
        'transferred' => ['bg' => 'rgba(16,24,40,0.1)',    'color' => '#101828', 'fill' => '#475569'],
    ];

    // Prepare mobile calendar JSON data
    $mcalJson = [];
    foreach ($eventsByDay as $day => $events) {
        $mcalJson[$day] = [];
        foreach ($events as $ev) {
            $sc = $statusColors[$ev->status] ?? ['fill' => '#999', 'bg' => '#eee', 'color' => '#666'];
            $mcalJson[$day][] = [
                'status'          => $ev->status,
                'saleNumber'      => $ev->sale_number,
                'unitCode'        => $ev->unit_code ?? '-',
                'userName'        => $ev->user_name ?? '-',
                'appointmentDate' => $ev->appointment_date ?? '',
                'appointmentTime' => $ev->appointment_time ?? '',
                'remark'          => $ev->remark ?? '',
                'saleId'          => $ev->sale_id,
                'fill'            => $sc['fill'],
                'bg'              => $sc['bg'],
                'color'           => $sc['color'],
            ];
        }
    }
@endphp

@section('content')
    <div class="page-header">
        <h3>Activity</h3>
    </div>

    {{-- ── Calendar View ─────────────────────────────────────────── --}}
    <div class="section-card">
        <div class="section-card-header">
            <i class="bi bi-calendar3"></i> Calendar
            <div class="month-nav">
                <a href="{{ route('activity.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}"><i class="bi bi-chevron-left"></i></a>
                @unless($isCurrentMonth)
                    <a href="{{ route('activity.index') }}" class="today-btn">Today</a>
                @endunless
                <span>{{ $monthLabel }}</span>
                <a href="{{ route('activity.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}"><i class="bi bi-chevron-right"></i></a>
            </div>
        </div>
        <div class="section-card-body" style="padding: 0.75rem;">
            <table class="cal-table">
                <thead>
                    <tr>
                        <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dayCounter = 1;
                        $totalCells = ceil(($firstDayOfWeek + $daysInMonth) / 7) * 7;
                    @endphp
                    @for($cell = 0; $cell < $totalCells; $cell++)
                        @if($cell % 7 === 0)<tr>@endif

                        @php
                            $dayNum = $cell - $firstDayOfWeek + 1;
                            $isValid = ($dayNum >= 1 && $dayNum <= $daysInMonth);
                            $isToday = $isValid && $isCurrentMonth && $dayNum == $now->day;
                            $dayEvents = $isValid ? ($eventsByDay[$dayNum] ?? []) : [];
                            $maxShow = 3;
                        @endphp

                        <td class="{{ !$isValid ? 'cal-empty' : '' }}">
                            @if($isValid)
                                <div class="cal-day-num {{ $isToday ? 'today' : '' }}">{{ $dayNum }}</div>
                                @foreach(array_slice($dayEvents, 0, $maxShow) as $ev)
                                    @php
                                        $sc = $statusColors[$ev->status] ?? ['bg' => '#eee', 'color' => '#666'];
                                        $evTime = ($ev->status === 'appointment' && $ev->appointment_time)
                                            ? \Carbon\Carbon::parse($ev->appointment_time)->format('H:i') . ' '
                                            : '';
                                        $evLabel = $evTime . ($ev->unit_code ?? $ev->sale_number);
                                    @endphp
                                    <span class="cal-event cal-event-click" style="background: {{ $sc['bg'] }}; color: {{ $sc['color'] }}; cursor: pointer;"
                                       data-status="{{ $ev->status }}"
                                       data-sale-number="{{ $ev->sale_number }}"
                                       data-unit-code="{{ $ev->unit_code ?? '-' }}"
                                       data-user-name="{{ $ev->user_name ?? '-' }}"
                                       data-appointment-date="{{ $ev->appointment_date ?? '' }}"
                                       data-appointment-time="{{ $ev->appointment_time ?? '' }}"
                                       data-remark="{{ $ev->remark ?? '' }}"
                                       data-sale-id="{{ $ev->sale_id }}">
                                        {{ $evLabel }}
                                    </span>
                                @endforeach
                                @if(count($dayEvents) > $maxShow)
                                    <span class="cal-more">+{{ count($dayEvents) - $maxShow }} more</span>
                                @endif
                            @endif
                        </td>

                        @if($cell % 7 === 6)</tr>@endif
                    @endfor
                </tbody>
            </table>

            {{-- ── Mobile Calendar (iOS-style) ── --}}
            <div class="mobile-cal">
                <div class="mcal-grid">
                    <div class="mcal-head">Sun</div><div class="mcal-head">Mon</div><div class="mcal-head">Tue</div>
                    <div class="mcal-head">Wed</div><div class="mcal-head">Thu</div><div class="mcal-head">Fri</div>
                    <div class="mcal-head">Sat</div>

                    @for($cell = 0; $cell < $totalCells; $cell++)
                        @php
                            $dayNum = $cell - $firstDayOfWeek + 1;
                            $isValid = ($dayNum >= 1 && $dayNum <= $daysInMonth);
                            $isTodayMcal = $isValid && $isCurrentMonth && $dayNum == $now->day;
                            $dayEvMcal = $isValid ? ($eventsByDay[$dayNum] ?? []) : [];
                        @endphp
                        <div class="mcal-day{{ !$isValid ? ' other-month' : '' }}{{ $isTodayMcal ? ' is-today' : '' }}"
                             data-day="{{ $isValid ? $dayNum : '' }}">
                            <span class="mcal-day-num">{{ $isValid ? $dayNum : '' }}</span>
                            @if($isValid && count($dayEvMcal))
                                <div class="mcal-dots">
                                    @foreach(array_slice(array_unique(array_column($dayEvMcal, 'status')), 0, 3) as $st)
                                        <span class="mcal-dot" style="background: {{ ($statusColors[$st] ?? ['fill' => '#999'])['fill'] }};"></span>
                                    @endforeach
                                </div>
                            @else
                                <div class="mcal-dots"></div>
                            @endif
                        </div>
                    @endfor
                </div>

                <div class="mcal-events" id="mcalEvents"></div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- ── Activities Summary (horizontal bars) ──────────────── --}}
        <div class="col-lg-5">
            <div class="section-card">
                <div class="section-card-header">
                    <i class="bi bi-lightning-charge"></i> Activities
                    <span style="font-size: 0.72rem; font-weight: 400; color: var(--text-light); margin-left: auto;">
                        {{ $monthLabel }} · {{ $activityTotal }} total
                    </span>
                </div>
                <div class="section-card-body">
                    @foreach($activityBars as $status => $cnt)
                        @php
                            $sc = $statusColors[$status] ?? ['fill' => '#999'];
                            $pct = round($cnt / $activityMax * 100);
                        @endphp
                        <div class="act-bar-item">
                            <div class="act-bar-top">
                                <span class="act-bar-label">{{ ucfirst($status) }}</span>
                                <span class="act-bar-count">{{ $cnt }}</span>
                            </div>
                            <div class="act-bar-track">
                                @if($cnt > 0)
                                    <div class="act-bar-fill" style="width: {{ $pct }}%; background: {{ $sc['fill'] }};"></div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Upcoming Appointments ─────────────────────────────── --}}
        <div class="col-lg-7">
            <div class="section-card">
                <div class="section-card-header">
                    <i class="bi bi-calendar-event"></i> Upcoming Appointments
                </div>
                <div class="section-card-body" style="padding: 0.5rem 1.25rem;">
                    @if($appointments->count())
                        <ul class="appt-list">
                            @foreach($appointments as $appt)
                                @php
                                    $apptDate = \Carbon\Carbon::parse($appt->appointment_date);
                                    $isToday = $apptDate->isToday();
                                @endphp
                                <li class="appt-item">
                                    <div class="appt-date-block {{ $isToday ? 'today' : '' }}">
                                        <div class="appt-date-day">{{ $apptDate->format('d') }}</div>
                                        <div class="appt-date-mon">{{ $apptDate->format('M') }}</div>
                                    </div>
                                    <div class="appt-body">
                                        <div>
                                            <span class="appt-title">{{ $appt->sale_number }}</span>
                                            <span class="appt-unit">{{ $appt->unit_code }}</span>
                                            @if($isToday)
                                                <span class="appt-today-badge ms-1">Today</span>
                                            @endif
                                        </div>
                                        <div class="appt-detail">
                                            @if($appt->appointment_time)
                                                <i class="bi bi-clock"></i>{{ \Carbon\Carbon::parse($appt->appointment_time)->format('H:i') }}
                                            @endif
                                            @if(!empty($appt->appointment_remark))
                                                <span class="ms-2 text-muted">{{ Str::limit($appt->appointment_remark, 50) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="appt-user">{{ $appt->user_name ?? '—' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="empty-state">
                            <i class="bi bi-calendar-x"></i>
                            No upcoming appointments.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Event Detail Popup ── --}}
    <div class="cal-popup-overlay" id="calPopupOverlay">
        <div class="cal-popup">
            <button class="cal-popup-close" id="calPopupClose">&times;</button>
            <div id="calPopupContent"></div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
(function () {
    /* ── Mobile Calendar Logic ── */
    const mcalEventsData = @json($mcalJson);

    const mcalContainer = document.getElementById('mcalEvents');
    const mcalDays = document.querySelectorAll('.mcal-day[data-day]');

    function mcalSelect(dayEl) {
        document.querySelectorAll('.mcal-day.selected').forEach(d => d.classList.remove('selected'));
        if (dayEl) dayEl.classList.add('selected');

        const day = dayEl ? dayEl.dataset.day : null;
        const events = day ? (mcalEventsData[day] || []) : [];

        if (!mcalContainer) return;

        if (!day || !events.length) {
            mcalContainer.innerHTML = '<div class="mcal-no-events"><i class="bi bi-calendar-x" style="font-size:1.25rem;display:block;margin-bottom:0.3rem;"></i>No activities on this day.</div>';
            return;
        }

        const dateObj = new Date({{ $year }}, {{ $month - 1 }}, parseInt(day));
        const headerText = dateObj.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long' });

        let html = `<div class="mcal-events-header">${headerText}</div>`;
        events.forEach(ev => {
            const timeStr = (ev.status === 'appointment' && ev.appointmentTime)
                ? ev.appointmentTime.substring(0, 5) + ' · '
                : '';
            html += `<div class="mcal-event-card cal-event-click"
                data-status="${ev.status}"
                data-sale-number="${ev.saleNumber}"
                data-unit-code="${ev.unitCode}"
                data-user-name="${ev.userName}"
                data-appointment-date="${ev.appointmentDate}"
                data-appointment-time="${ev.appointmentTime}"
                data-remark="${ev.remark}"
                data-sale-id="${ev.saleId}">
                <span class="mcal-event-dot-lg" style="background:${ev.fill};"></span>
                <div class="mcal-event-body">
                    <div class="mcal-event-title">${ev.unitCode} <span style="font-weight:400;color:var(--text-light);font-size:0.78rem;">${ev.saleNumber}</span></div>
                    <div class="mcal-event-sub">${timeStr}${ev.userName}</div>
                </div>
                <span class="mcal-event-badge" style="background:${ev.bg};color:${ev.color};">${ev.status}</span>
            </div>`;
        });
        mcalContainer.innerHTML = html;
        bindPopupClicks();
    }

    mcalDays.forEach(d => {
        if (d.dataset.day) d.addEventListener('click', () => mcalSelect(d));
    });

    // Default select: today or first day with events
    const todayEl = document.querySelector('.mcal-day.is-today[data-day]');
    if (todayEl) {
        mcalSelect(todayEl);
    } else {
        const firstWithEvents = Array.from(mcalDays).find(d => d.dataset.day && mcalEventsData[d.dataset.day]?.length);
        if (firstWithEvents) mcalSelect(firstWithEvents);
    }
    const overlay = document.getElementById('calPopupOverlay');
    const content = document.getElementById('calPopupContent');
    const closeBtn = document.getElementById('calPopupClose');
    if (!overlay) return;

    const statusLabels = {
        appointment: { label: 'Appointment', icon: 'bi-calendar-event', cls: 'appointment' },
        transferred: { label: 'Transferred', icon: 'bi-patch-check', cls: 'transferred' },
    };

    function openPopup(d) {
        const info = statusLabels[d.status] || { label: d.status, icon: 'bi-circle', cls: '' };

        let html = '';
        html += `<div class="cpop-header">`;
        html += `<span class="cpop-status ${info.cls}"><i class="bi ${info.icon}"></i> ${info.label}</span>`;
        html += `</div>`;

        html += `<div class="cpop-body">`;
        html += `<div class="cpop-row"><span class="cpop-label">Sale No.</span><span class="cpop-value">${d.saleNumber}</span></div>`;
        html += `<div class="cpop-row"><span class="cpop-label">Unit</span><span class="cpop-value">${d.unitCode}</span></div>`;
        html += `<div class="cpop-row"><span class="cpop-label">Agent</span><span class="cpop-value">${d.userName}</span></div>`;

        if (d.status === 'appointment') {
            if (d.appointmentDate) {
                const dt = new Date(d.appointmentDate);
                const dateStr = dt.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
                html += `<div class="cpop-row"><span class="cpop-label">Date</span><span class="cpop-value">${dateStr}</span></div>`;
            }
            if (d.appointmentTime) {
                const timeParts = d.appointmentTime.split(':');
                html += `<div class="cpop-row"><span class="cpop-label">Time</span><span class="cpop-value">${timeParts[0]}:${timeParts[1]}</span></div>`;
            }
            if (d.remark) {
                html += `<div class="cpop-row cpop-row-full"><span class="cpop-label">Remark</span><span class="cpop-value">${d.remark}</span></div>`;
            }
        }

        html += `</div>`;

        html += `<div class="cpop-footer">`;
        html += `<a href="/buy-sale?highlight=${d.saleId}" class="cpop-link"><i class="bi bi-box-arrow-up-right me-1"></i>View in Buy/Sale</a>`;
        html += `</div>`;

        content.innerHTML = html;
        overlay.classList.add('active');
    }

    function bindPopupClicks() {
        document.querySelectorAll('.cal-event-click').forEach(el => {
            if (el._popupBound) return;
            el._popupBound = true;
            el.addEventListener('click', function (e) {
                e.preventDefault();
                openPopup(this.dataset);
            });
        });
    }
    bindPopupClicks();

    closeBtn.addEventListener('click', () => overlay.classList.remove('active'));
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) overlay.classList.remove('active');
    });
})();
</script>
@endsection

