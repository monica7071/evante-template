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
        // ── KPI Cards (3): Transferred / Reserved / Contract — value + unit count ──
        $kpiStatuses = ['transferred', 'reserved', 'contract'];
        $kpis = (object) [];
        foreach ($kpiStatuses as $status) {
            $query = DB::table('sales')
                ->join('listings', 'sales.listing_id', '=', 'listings.id')
                ->where('sales.status', $status);

            if ($status === 'transferred') {
                $query->whereMonth('sales.created_at', $currentMonth)
                    ->whereYear('sales.created_at', $currentYear);
            }

            $row = $query->select(
                DB::raw('COALESCE(SUM(listings.price_per_room), 0) as total_value'),
                DB::raw('COUNT(*) as unit_count')
            )->first();

            $kpis->$status = (object) [
                'value' => (float) $row->total_value,
                'units' => (int) $row->unit_count,
            ];
        }

        // ── Yearly sales chart (Jan-Dec) — value by month per status ──
        $yearlyLabels = [];
        for ($i = 1; $i <= 12; $i++) {
            $yearlyLabels[] = Carbon::create($currentYear, $i)->format('M');
        }

        $chartStatuses = ['transferred', 'contract', 'reserved'];
        $yearlyValues = [];
        $yearlyCountValues = [];
        $yearlyTotal = 0;

        foreach ($chartStatuses as $status) {
            $rawValues = DB::table('sales')
                ->join('listings', 'sales.listing_id', '=', 'listings.id')
                ->where('sales.status', $status)
                ->whereYear('sales.created_at', $currentYear)
                ->select(
                    DB::raw('MONTH(sales.created_at) as m'),
                    DB::raw('SUM(listings.price_per_room) as val')
                )
                ->groupBy('m')
                ->pluck('val', 'm')
                ->toArray();

            $rawCounts = DB::table('sales')
                ->where('status', $status)
                ->whereYear('created_at', $currentYear)
                ->select(
                    DB::raw('MONTH(created_at) as m'),
                    DB::raw('COUNT(*) as cnt')
                )
                ->groupBy('m')
                ->pluck('cnt', 'm')
                ->toArray();

            $values = [];
            $counts = [];
            for ($i = 1; $i <= 12; $i++) {
                $v = (float) ($rawValues[$i] ?? 0);
                $values[] = $v;
                $counts[] = (int) ($rawCounts[$i] ?? 0);
                if ($status === 'transferred') {
                    $yearlyTotal += $v;
                }
            }
            $yearlyValues[$status] = $values;
            $yearlyCountValues[$status] = $counts;
        }

        $yearlyChart = (object) [
            'labels' => $yearlyLabels,
            'transferred' => $yearlyValues['transferred'],
            'contract' => $yearlyValues['contract'],
            'reserved' => $yearlyValues['reserved'],
            'countTransferred' => $yearlyCountValues['transferred'],
            'countContract' => $yearlyCountValues['contract'],
            'countReserved' => $yearlyCountValues['reserved'],
            'total' => $yearlyTotal,
            'year' => $currentYear,
        ];

        // ── Top 5 sale performance (both views queried for client-side toggle) ──
        $top5BaseJoin = fn () => DB::table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->leftJoin('teams', 'employees.team_id', '=', 'teams.id')
            ->where('users.role', '!=', 'agent')
            ->where('sales.status', 'transferred');

        $top5Transferred = $top5BaseJoin()
            ->whereMonth('sales.created_at', $currentMonth)
            ->whereYear('sales.created_at', $currentYear)
            ->select('users.id as user_id', 'users.name', 'teams.name as team_name', DB::raw('SUM(listings.price_per_room) as total_value'))
            ->groupBy('users.id', 'users.name', 'teams.name')
            ->orderByDesc('total_value')
            ->limit(5)
            ->get();

        $top5Units = $top5BaseJoin()
            ->select('users.id as user_id', 'users.name', 'teams.name as team_name', DB::raw('COUNT(*) as total_units'))
            ->groupBy('users.id', 'users.name', 'teams.name')
            ->orderByDesc('total_units')
            ->limit(5)
            ->get();

        // ── Upcoming Appointments ──────────────────────────────────
        $appointments = DB::table('sales')
            ->join('sale_appointments', 'sales.id', '=', 'sale_appointments.sale_id')
            ->leftJoin('listings', 'sales.listing_id', '=', 'listings.id')
            ->leftJoin('users', 'sales.user_id', '=', 'users.id')
            ->where('sales.status', 'appointment')
            ->whereNotNull('sale_appointments.appointment_date')
            ->where('sale_appointments.appointment_date', '>=', $now->toDateString())
            ->select(
                'sales.sale_number',
                'sale_appointments.appointment_date',
                'sale_appointments.appointment_time',
                'listings.unit_code',
                'users.name as user_name',
            )
            ->orderBy('sale_appointments.appointment_date')
            ->orderBy('sale_appointments.appointment_time')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'kpis', 'yearlyChart', 'top5Transferred', 'top5Units', 'appointments',
            'currentYear', 'currentMonth'
        ));
    }
}
