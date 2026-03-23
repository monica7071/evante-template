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

    @media (max-width: 767px) {
        .cal-table td { height: 60px; padding: 0.2rem; }
        .cal-event { font-size: 0.55rem; }
    }
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
                                    @php $sc = $statusColors[$ev->status] ?? ['bg' => '#eee', 'color' => '#666']; @endphp
                                    <span class="cal-event" style="background: {{ $sc['bg'] }}; color: {{ $sc['color'] }};"
                                          title="{{ $ev->sale_number }} · {{ $ev->unit_code }} · {{ ucfirst($ev->status) }}">
                                        {{ $ev->sale_number }}
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
@endsection
