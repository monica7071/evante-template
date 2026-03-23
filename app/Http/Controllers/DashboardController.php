<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $view = $request->get('view', 'transferred'); // transferred | active

        // ── KPI Cards (3): Transferred / Reserved / Contract value this month ──
        $kpis = (object) [
            'transferred' => DB::table('sales')
                ->join('listings', 'sales.listing_id', '=', 'listings.id')
                ->where('sales.status', 'transferred')
                ->whereMonth('sales.created_at', $currentMonth)
                ->whereYear('sales.created_at', $currentYear)
                ->sum('listings.price_per_room'),
            'reserved' => DB::table('sales')
                ->join('listings', 'sales.listing_id', '=', 'listings.id')
                ->where('sales.status', 'reserved')
                ->sum('listings.price_per_room'),
            'contract' => DB::table('sales')
                ->join('listings', 'sales.listing_id', '=', 'listings.id')
                ->where('sales.status', 'contract')
                ->sum('listings.price_per_room'),
        ];

        // ── Yearly sales chart (Jan-Dec, transferred value by month) ──
        $yearlyRaw = DB::table('sales')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->where('sales.status', 'transferred')
            ->whereYear('sales.created_at', $currentYear)
            ->select(
                DB::raw('MONTH(sales.created_at) as m'),
                DB::raw('SUM(listings.price_per_room) as val')
            )
            ->groupBy('m')
            ->pluck('val', 'm')
            ->toArray();

        $yearlyLabels = [];
        $yearlyValues = [];
        $yearlyTotal = 0;
        for ($i = 1; $i <= 12; $i++) {
            $yearlyLabels[] = Carbon::create($currentYear, $i)->format('M');
            $v = (float)($yearlyRaw[$i] ?? 0);
            $yearlyValues[] = $v;
            $yearlyTotal += $v;
        }

        $yearlyChart = (object) [
            'labels' => $yearlyLabels,
            'values' => $yearlyValues,
            'total' => $yearlyTotal,
            'year' => $currentYear,
        ];

        // ── Top 5 sale performance ─────────────────────────────────
        $top5Query = DB::table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->leftJoin('teams', 'employees.team_id', '=', 'teams.id')
            ->where('users.role', '!=', 'agent');

        if ($view === 'active') {
            $top5Query->whereIn('sales.status', ['appointment', 'reserved', 'contract', 'installment']);
        } else {
            $top5Query->where('sales.status', 'transferred')
                ->whereMonth('sales.created_at', $currentMonth)
                ->whereYear('sales.created_at', $currentYear);
        }

        $top5 = $top5Query->select(
            'users.id as user_id',
            'users.name',
            'teams.name as team_name',
            DB::raw('SUM(listings.price_per_room) as total_value')
        )
            ->groupBy('users.id', 'users.name', 'teams.name')
            ->orderByDesc('total_value')
            ->limit(5)
            ->get();

        // ── Upcoming Appointments ──────────────────────────────────
        $appointments = DB::table('sales')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->leftJoin('users', 'sales.user_id', '=', 'users.id')
            ->where('sales.status', 'appointment')
            ->whereNotNull('sales.appointment_date')
            ->where('sales.appointment_date', '>=', $now->toDateString())
            ->select(
                'sales.sale_number',
                'sales.appointment_date',
                'sales.appointment_time',
                'listings.unit_code',
                'users.name as user_name',
            )
            ->orderBy('sales.appointment_date')
            ->orderBy('sales.appointment_time')
            ->limit(10)
            ->get();

        // ── Activities Summary (count per status) ──────────────────
        $activitySummary = DB::table('sales')
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();
        $activityTotal = array_sum($activitySummary);

        return view('dashboard', compact(
            'kpis', 'yearlyChart', 'top5', 'appointments',
            'activitySummary', 'activityTotal',
            'currentYear', 'currentMonth', 'view'
        ));
    }
}
