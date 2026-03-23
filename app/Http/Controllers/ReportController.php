<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $month = (int) $request->get('month', $now->month);
        $year = (int) $request->get('year', $now->year);
        $teamId = $request->get('team_id');
        $view = $request->get('view', 'transferred'); // transferred | active

        $teamChartMonth = (int) $request->get('team_chart_month', $month);
        $teamChartYear = (int) $request->get('team_chart_year', $year);
        $personChartMonth = (int) $request->get('person_chart_month', $month);
        $personChartYear = (int) $request->get('person_chart_year', $year);

        $teams = Team::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $filters = compact(
            'month', 'year', 'teamId', 'view',
            'teamChartMonth', 'teamChartYear', 'personChartMonth', 'personChartYear'
        );

        // Date range for the selected month
        $dateFrom = Carbon::create($year, $month, 1)->startOfMonth();
        $dateTo = $dateFrom->copy()->endOfMonth();

        // ── 1. Sale Value by Team (donut chart) ─────────────────────
        $teamChartDateFrom = Carbon::create($teamChartYear, $teamChartMonth, 1)->startOfMonth();
        $teamChartDateTo = $teamChartDateFrom->copy()->endOfMonth();

        $teamChartQuery = DB::table('sales')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->leftJoin('teams', 'employees.team_id', '=', 'teams.id')
            ->where('users.role', '!=', 'agent');

        if ($view === 'active') {
            $teamChartQuery->whereIn('sales.status', ['appointment', 'reserved', 'contract', 'installment']);
        } else {
            $teamChartQuery->where('sales.status', 'transferred');
        }
        $teamChartQuery->whereBetween('sales.created_at', [$teamChartDateFrom, $teamChartDateTo]);

        $teamChart = $teamChartQuery->select(
            DB::raw("COALESCE(teams.name, 'No Team') as team_name"),
            DB::raw('SUM(listings.price_per_room) as total_value'),
            DB::raw('COUNT(sales.id) as deal_count')
        )
            ->groupBy('teams.name')
            ->orderByDesc('total_value')
            ->get();

        // ── 2. Sale Value by Person (donut chart) ───────────────────
        $personChartDateFrom = Carbon::create($personChartYear, $personChartMonth, 1)->startOfMonth();
        $personChartDateTo = $personChartDateFrom->copy()->endOfMonth();

        $saleChartQuery = DB::table('sales')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->leftJoin('teams', 'employees.team_id', '=', 'teams.id')
            ->where('users.role', '!=', 'agent');

        if ($teamId) {
            $saleChartQuery->where('employees.team_id', $teamId);
        }

        if ($view === 'active') {
            $saleChartQuery->whereIn('sales.status', ['appointment', 'reserved', 'contract', 'installment']);
        } else {
            $saleChartQuery->where('sales.status', 'transferred');
        }
        $saleChartQuery->whereBetween('sales.created_at', [$personChartDateFrom, $personChartDateTo]);

        $saleChart = $saleChartQuery->select(
            'users.id as user_id',
            'users.name',
            'teams.name as team_name',
            DB::raw('SUM(listings.price_per_room) as total_value'),
            DB::raw('COUNT(sales.id) as deal_count')
        )
            ->groupBy('users.id', 'users.name', 'teams.name')
            ->orderByDesc('total_value')
            ->get();

        // ── 3. Top 5 Ranked List ────────────────────────────────────
        $top5Query = DB::table('sales')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->leftJoin('teams', 'employees.team_id', '=', 'teams.id')
            ->where('users.role', '!=', 'agent');

        if ($teamId) {
            $top5Query->where('employees.team_id', $teamId);
        }

        if ($view === 'active') {
            $top5Query->whereIn('sales.status', ['appointment', 'reserved', 'contract', 'installment']);
        } else {
            $top5Query->where('sales.status', 'transferred')
                ->whereBetween('sales.created_at', [$dateFrom, $dateTo]);
        }

        $top5 = $top5Query->select(
            'users.id as user_id',
            'users.name',
            'teams.name as team_name',
            DB::raw('SUM(listings.price_per_room) as total_value'),
            DB::raw('COUNT(sales.id) as deal_count')
        )
            ->groupBy('users.id', 'users.name', 'teams.name')
            ->orderByDesc('total_value')
            ->limit(5)
            ->get();

        // ── 4. Unit Type Horizontal Bars ────────────────────────────
        $unitTypeQuery = DB::table('sales')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->where('sales.status', '!=', 'available');

        if ($view === 'transferred') {
            $unitTypeQuery->where('sales.status', 'transferred')
                ->whereBetween('sales.created_at', [$dateFrom, $dateTo]);
        }

        $unitTypeBar = $unitTypeQuery->select(
            'listings.unit_type',
            DB::raw('COUNT(sales.id) as cnt'),
            DB::raw('SUM(listings.price_per_room) as val')
        )
            ->whereNotNull('listings.unit_type')
            ->groupBy('listings.unit_type')
            ->orderByDesc('cnt')
            ->get();

        $unitTypeMax = $unitTypeBar->max('cnt') ?: 1;

        // ── 5. Customer Type Split (Bank Loan vs Cash) ──────────────
        $custQuery = DB::table('sale_purchase_agreements')
            ->join('sales', 'sale_purchase_agreements.sale_id', '=', 'sales.id')
            ->join('listings', 'sales.listing_id', '=', 'listings.id');

        if ($view === 'transferred') {
            $custQuery->where('sales.status', 'transferred')
                ->whereBetween('sales.created_at', [$dateFrom, $dateTo]);
        } else {
            $custQuery->whereIn('sales.status', ['appointment', 'reserved', 'contract', 'installment']);
        }

        $customerSplit = $custQuery->select(
            DB::raw("SUM(CASE WHEN sale_purchase_agreements.is_bank_loan = 1 THEN 1 ELSE 0 END) as bank_loan_count"),
            DB::raw("SUM(CASE WHEN sale_purchase_agreements.is_bank_loan = 1 THEN listings.price_per_room ELSE 0 END) as bank_loan_value"),
            DB::raw("SUM(CASE WHEN sale_purchase_agreements.is_cash_transfer = 1 THEN 1 ELSE 0 END) as cash_count"),
            DB::raw("SUM(CASE WHEN sale_purchase_agreements.is_cash_transfer = 1 THEN listings.price_per_room ELSE 0 END) as cash_value"),
            DB::raw('COUNT(*) as total_count')
        )->first();

        // ── 6. Production Line Chart (monthly, full year) ───────────
        $prodRaw = DB::table('sales')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->where('sales.status', 'transferred')
            ->whereYear('sales.created_at', $year)
            ->select(
                DB::raw('MONTH(sales.created_at) as m'),
                DB::raw('SUM(listings.price_per_room) as val'),
                DB::raw('COUNT(sales.id) as cnt')
            )
            ->groupBy('m')
            ->pluck('val', 'm')
            ->toArray();

        $productionChart = (object) [
            'labels' => [],
            'values' => [],
        ];
        for ($i = 1; $i <= 12; $i++) {
            $productionChart->labels[] = Carbon::create($year, $i)->format('M');
            $productionChart->values[] = (float) ($prodRaw[$i] ?? 0);
        }

        return view('report.index', compact(
            'teamChart', 'saleChart', 'top5', 'unitTypeBar', 'unitTypeMax',
            'customerSplit', 'productionChart', 'teams', 'filters',
            'year', 'month',
            'teamChartMonth', 'teamChartYear', 'personChartMonth', 'personChartYear'
        ));
    }
}
