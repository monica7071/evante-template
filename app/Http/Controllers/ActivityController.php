<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $year = (int) $request->get('year', $now->year);
        $month = (int) $request->get('month', $now->month);

        $calendarDate = Carbon::create($year, $month, 1);

        // ── Calendar Data ────────────────────────────────────────
        // Get all status changes in this month for the calendar grid
        $monthStart = $calendarDate->copy()->startOfMonth();
        $monthEnd = $calendarDate->copy()->endOfMonth();

        $calendarEvents = DB::table('status_histories')
            ->join('sales', 'status_histories.sale_id', '=', 'sales.id')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->leftJoin('users', 'status_histories.user_id', '=', 'users.id')
            ->whereBetween('status_histories.created_at', [$monthStart, $monthEnd])
            ->select(
                DB::raw('DAY(status_histories.created_at) as day'),
                'status_histories.status',
                'sales.sale_number',
                'listings.unit_code',
                'users.name as user_name',
            )
            ->orderBy('status_histories.created_at')
            ->get();

        // Group events by day
        $eventsByDay = [];
        foreach ($calendarEvents as $ev) {
            $eventsByDay[$ev->day][] = $ev;
        }

        // Build calendar grid
        $firstDayOfWeek = $monthStart->dayOfWeek; // 0=Sun
        $daysInMonth = $calendarDate->daysInMonth;

        // ── Activities Summary (horizontal bars, for selected month) ─
        $activitySummary = DB::table('status_histories')
            ->whereBetween('status_histories.created_at', [$monthStart, $monthEnd])
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        $statuses = ['available', 'appointment', 'reserved', 'contract', 'installment', 'transferred'];
        $activityBars = [];
        $activityMax = 1;
        foreach ($statuses as $s) {
            $cnt = $activitySummary[$s] ?? 0;
            $activityBars[$s] = $cnt;
            if ($cnt > $activityMax) $activityMax = $cnt;
        }
        $activityTotal = array_sum($activityBars);

        // ── Upcoming Appointments ────────────────────────────────
        $appointments = DB::table('sales')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->leftJoin('users', 'sales.user_id', '=', 'users.id')
            ->where('sales.status', 'appointment')
            ->whereNotNull('sales.appointment_date')
            ->where('sales.appointment_date', '>=', $now->toDateString())
            ->select(
                'sales.id as sale_id',
                'sales.sale_number',
                'sales.appointment_date',
                'sales.appointment_time',
                'sales.appointment_name',
                'sales.appointment_phone',
                'listings.unit_code',
                'users.name as user_name',
            )
            ->orderBy('sales.appointment_date')
            ->orderBy('sales.appointment_time')
            ->limit(15)
            ->get();

        return view('activity.index', compact(
            'eventsByDay', 'firstDayOfWeek', 'daysInMonth',
            'activityBars', 'activityMax', 'activityTotal',
            'appointments', 'year', 'month', 'calendarDate'
        ));
    }
}
