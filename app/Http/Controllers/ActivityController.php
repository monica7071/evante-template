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
        $monthStart = $calendarDate->copy()->startOfMonth();
        $monthEnd = $calendarDate->copy()->endOfMonth();

        // Transferred events from status_histories
        $transferredEvents = DB::table('status_histories')
            ->join('sales', 'status_histories.sale_id', '=', 'sales.id')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->leftJoin('users', 'status_histories.user_id', '=', 'users.id')
            ->where('status_histories.status', 'transferred')
            ->whereBetween('status_histories.created_at', [$monthStart, $monthEnd])
            ->select(
                DB::raw('DAY(status_histories.created_at) as day'),
                'status_histories.status',
                'sales.id as sale_id',
                'sales.sale_number',
                'listings.unit_code',
                'users.name as user_name',
                DB::raw('NULL as appointment_date'),
                DB::raw('NULL as appointment_time'),
                DB::raw('NULL as remark'),
            )
            ->orderBy('status_histories.created_at')
            ->get();

        // Appointment events from sale_appointments
        $appointmentEvents = DB::table('sale_appointments')
            ->join('sales', 'sale_appointments.sale_id', '=', 'sales.id')
            ->leftJoin('listings', 'sales.listing_id', '=', 'listings.id')
            ->leftJoin('users', 'sales.user_id', '=', 'users.id')
            ->whereBetween('sale_appointments.appointment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->select(
                DB::raw('DAY(sale_appointments.appointment_date) as day'),
                DB::raw("'appointment' as status"),
                'sales.id as sale_id',
                'sales.sale_number',
                'listings.unit_code',
                'users.name as user_name',
                'sale_appointments.appointment_date',
                'sale_appointments.appointment_time',
                'sale_appointments.remark',
            )
            ->orderBy('sale_appointments.appointment_date')
            ->orderBy('sale_appointments.appointment_time')
            ->get();

        // Merge and group by day
        $eventsByDay = [];
        foreach ($transferredEvents->merge($appointmentEvents) as $ev) {
            $eventsByDay[$ev->day][] = $ev;
        }

        // Build calendar grid
        $firstDayOfWeek = $monthStart->dayOfWeek; // 0=Sun
        $daysInMonth = $calendarDate->daysInMonth;

        // ── Activities Summary (horizontal bars, for selected month) ─
        // Appointment count from sale_appointments table
        $appointmentCount = (int) DB::table('sale_appointments')
            ->whereBetween('appointment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->count();

        // Transferred count from status_histories table
        $transferredCount = (int) DB::table('status_histories')
            ->where('status', 'transferred')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();

        $activityBars = [
            'appointment' => $appointmentCount,
            'transferred' => $transferredCount,
        ];
        $activityMax = max($appointmentCount, $transferredCount, 1);
        $activityTotal = $appointmentCount + $transferredCount;

        // ── Upcoming Appointments ────────────────────────────────
        $appointments = DB::table('sales')
            ->join('sale_appointments', 'sales.id', '=', 'sale_appointments.sale_id')
            ->leftJoin('listings', 'sales.listing_id', '=', 'listings.id')
            ->leftJoin('users', 'sales.user_id', '=', 'users.id')
            ->where('sales.status', 'appointment')
            ->whereNotNull('sale_appointments.appointment_date')
            ->where('sale_appointments.appointment_date', '>=', $now->toDateString())
            ->select(
                'sales.id as sale_id',
                'sales.sale_number',
                'sale_appointments.appointment_date',
                'sale_appointments.appointment_time',
                'sale_appointments.remark as appointment_remark',
                'listings.unit_code',
                'users.name as user_name',
            )
            ->orderBy('sale_appointments.appointment_date')
            ->orderBy('sale_appointments.appointment_time')
            ->limit(15)
            ->get();

        return view('activity.index', compact(
            'eventsByDay', 'firstDayOfWeek', 'daysInMonth',
            'activityBars', 'activityMax', 'activityTotal',
            'appointments', 'year', 'month', 'calendarDate'
        ));
    }
}
