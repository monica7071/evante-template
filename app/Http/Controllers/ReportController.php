<?php

namespace App\Http\Controllers;

use App\Models\ReportBudget;
use App\Models\Team;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);
        $data = $this->buildReportData($filters);

        $budgets = ReportBudget::where('year', $filters['year'])
            ->where('month', $filters['month'])
            ->orderBy('week')
            ->get();

        return view('report.index', array_merge($data, [
            'teams' => Team::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
            'year' => $filters['year'],
            'month' => $filters['month'],
            'teamChartMonth' => $filters['teamChartMonth'],
            'teamChartYear' => $filters['teamChartYear'],
            'personChartMonth' => $filters['personChartMonth'],
            'personChartYear' => $filters['personChartYear'],
            'budgets' => $budgets,
        ]));
    }

    public function saveBudget(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2099',
            'month' => 'required|integer|min:1|max:12',
            'week' => 'required|integer|min:1|max:4',
            'budget_marketing_online' => 'nullable|numeric|min:0',
            'budget_marketing_offline' => 'nullable|numeric|min:0',
        ]);

        ReportBudget::updateOrCreate(
            ['year' => $request->year, 'month' => $request->month, 'week' => $request->week],
            [
                'budget_marketing_online' => $request->budget_marketing_online ?? 0,
                'budget_marketing_offline' => $request->budget_marketing_offline ?? 0,
            ]
        );

        $budgets = ReportBudget::where('year', $request->year)
            ->where('month', $request->month)
            ->orderBy('week')
            ->get();

        return response()->json(['budgets' => $budgets]);
    }

    public function deleteBudget(Request $request, ReportBudget $budget)
    {
        $year = $budget->year;
        $month = $budget->month;
        $budget->delete();

        $budgets = ReportBudget::where('year', $year)
            ->where('month', $month)
            ->orderBy('week')
            ->get();

        return response()->json(['budgets' => $budgets]);
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->parseFilters($request);
        $data = $this->buildReportData($filters);

        $monthLabel = Carbon::create($filters['year'], $filters['month'])->format('M Y');

        $budgets = ReportBudget::where('year', $filters['year'])
            ->where('month', $filters['month'])
            ->orderBy('week')
            ->get();

        $pdf = Pdf::loadView('report.pdf', array_merge($data, [
            'filters' => $filters,
            'monthLabel' => $monthLabel,
            'year' => $filters['year'],
            'budgets' => $budgets,
        ]));

        // Register Sarabun Thai font
        $fontDir = storage_path('fonts/');
        $dompdf = $pdf->getDomPDF();
        $fontMetrics = $dompdf->getFontMetrics();
        $fontMetrics->registerFont(
            ['family' => 'sarabun', 'style' => 'normal', 'weight' => 'normal'],
            $fontDir . 'Sarabun-Regular.ttf'
        );
        $fontMetrics->registerFont(
            ['family' => 'sarabun', 'style' => 'normal', 'weight' => 'bold'],
            $fontDir . 'Sarabun-Bold.ttf'
        );

        $pdf->setPaper('a4', 'portrait');

        $filename = 'report-' . strtolower($monthLabel) . '.pdf';
        $filename = str_replace(' ', '-', $filename);

        return $pdf->download($filename);
    }

    // ─── Private helpers ─────────────────────────────────────────────

    private function parseFilters(Request $request): array
    {
        $now = Carbon::now();
        $month = (int) $request->get('month', $now->month);
        $year = (int) $request->get('year', $now->year);

        return [
            'month' => $month,
            'year' => $year,
            'teamId' => $request->get('team_id'),
            'view' => $request->get('view', 'transferred'),
            'teamChartMonth' => (int) $request->get('team_chart_month', $month),
            'teamChartYear' => (int) $request->get('team_chart_year', $year),
            'personChartMonth' => (int) $request->get('person_chart_month', $month),
            'personChartYear' => (int) $request->get('person_chart_year', $year),
        ];
    }

    private function buildReportData(array $filters): array
    {
        $month = $filters['month'];
        $year = $filters['year'];
        $teamId = $filters['teamId'];
        $view = $filters['view'];
        $teamChartMonth = $filters['teamChartMonth'];
        $teamChartYear = $filters['teamChartYear'];
        $personChartMonth = $filters['personChartMonth'];
        $personChartYear = $filters['personChartYear'];

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

        // ── 6. Customer Nationality Split (Thai vs Foreign) ────────
        $natQuery = DB::table('sales')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->leftJoin('reservations', 'reservations.listing_id', '=', 'sales.listing_id');

        if ($view === 'transferred') {
            $natQuery->where('sales.status', 'transferred')
                ->whereBetween('sales.created_at', [$dateFrom, $dateTo]);
        } else {
            $natQuery->whereIn('sales.status', ['appointment', 'reserved', 'contract', 'installment']);
        }

        $nationalitySplit = $natQuery->select(
            DB::raw("CASE WHEN reservations.buyer_id_type = 'passport' THEN 'Foreign' ELSE 'Thai' END as nationality"),
            DB::raw('COUNT(sales.id) as deal_count'),
            DB::raw('SUM(listings.price_per_room) as total_value')
        )
            ->groupBy('nationality')
            ->get()
            ->keyBy('nationality');

        // ── 7. Payment Type × Nationality ─────────────────────
        $payNatQuery = DB::table('sale_purchase_agreements')
            ->join('sales', 'sale_purchase_agreements.sale_id', '=', 'sales.id')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->leftJoin('reservations', 'reservations.listing_id', '=', 'sales.listing_id');

        if ($view === 'transferred') {
            $payNatQuery->where('sales.status', 'transferred')
                ->whereBetween('sales.created_at', [$dateFrom, $dateTo]);
        } else {
            $payNatQuery->whereIn('sales.status', ['appointment', 'reserved', 'contract', 'installment']);
        }

        $paymentByNationality = $payNatQuery->select(
            DB::raw("CASE WHEN reservations.buyer_id_type = 'passport' THEN 'Foreign' ELSE 'Thai' END as nationality"),
            DB::raw("CASE WHEN sale_purchase_agreements.is_bank_loan = 1 THEN 'Bank Loan' ELSE 'Cash Transfer' END as payment_type"),
            DB::raw('COUNT(*) as cnt'),
            DB::raw('SUM(listings.price_per_room) as val')
        )
            ->groupBy('nationality', 'payment_type')
            ->get();

        // ── 8. Production Line Chart (monthly, full year) ───────────
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

        return compact(
            'teamChart', 'saleChart', 'top5', 'unitTypeBar', 'unitTypeMax',
            'customerSplit', 'nationalitySplit', 'paymentByNationality', 'productionChart'
        );
    }
}
