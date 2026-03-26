<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleTransfer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $year = (int) $request->get('year', $now->year);
        $quarter = (int) $request->get('quarter', ceil($now->month / 3)); // 1-4

        // ── 4 KPI Mini-Cards ─────────────────────────────────────
        $kpis = (object) [
            'reservation_total' => DB::table('sales')
                ->join('listings', 'sales.listing_id', '=', 'listings.id')
                ->whereIn('sales.status', ['reserved', 'contract', 'installment', 'transferred'])
                ->whereYear('sales.created_at', $year)
                ->sum('listings.reservation_deposit'),

            'contract_payment_total' => DB::table('sales')
                ->join('listings', 'sales.listing_id', '=', 'listings.id')
                ->whereIn('sales.status', ['contract', 'installment', 'transferred'])
                ->whereYear('sales.created_at', $year)
                ->sum('listings.contract_payment'),

            'transfer_amount_total' => DB::table('sales')
                ->join('listings', 'sales.listing_id', '=', 'listings.id')
                ->where('sales.status', 'transferred')
                ->whereYear('sales.created_at', $year)
                ->sum('listings.transfer_amount'),

            'fees_total' => DB::table('sales')
                ->join('listings', 'sales.listing_id', '=', 'listings.id')
                ->where('sales.status', 'transferred')
                ->whereYear('sales.created_at', $year)
                ->select(DB::raw('COALESCE(SUM(listings.transfer_fee), 0) + COALESCE(SUM(listings.annual_common_fee), 0) + COALESCE(SUM(listings.sinking_fund), 0) as total'))
                ->value('total'),
        ];

        // ── Yearly Area Chart (Jan-Dec, transferred value) ───────
        $yearlyRaw = DB::table('sales')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->where('sales.status', 'transferred')
            ->whereYear('sales.created_at', $year)
            ->select(
                DB::raw('MONTH(sales.created_at) as m'),
                DB::raw('SUM(listings.price_per_room) as val')
            )
            ->groupBy('m')
            ->pluck('val', 'm')
            ->toArray();

        $yearlyChart = (object) ['labels' => [], 'values' => [], 'total' => 0];
        for ($i = 1; $i <= 12; $i++) {
            $yearlyChart->labels[] = Carbon::create($year, $i)->format('M');
            $v = (float) ($yearlyRaw[$i] ?? 0);
            $yearlyChart->values[] = $v;
            $yearlyChart->total += $v;
        }

        // Quarter-specific chart data (only 3 months)
        $qStartMonth = ($quarter - 1) * 3; // 0-indexed
        $quarterChartLabels = array_slice($yearlyChart->labels, $qStartMonth, 3);
        $quarterChartValues = array_slice($yearlyChart->values, $qStartMonth, 3);

        // ── Quarter Month Cards (3 months per selected quarter) ──
        $quarterMonths = [];
        $startMonth = ($quarter - 1) * 3 + 1;
        for ($m = $startMonth; $m < $startMonth + 3; $m++) {
            $mFrom = Carbon::create($year, $m, 1)->startOfMonth();
            $mTo = $mFrom->copy()->endOfMonth();

            $monthData = DB::table('sales')
                ->join('listings', 'sales.listing_id', '=', 'listings.id')
                ->where('sales.status', 'transferred')
                ->whereBetween('sales.created_at', [$mFrom, $mTo])
                ->select(
                    DB::raw('COUNT(sales.id) as deal_count'),
                    DB::raw('SUM(listings.price_per_room) as total_value')
                )
                ->first();

            $quarterMonths[] = (object) [
                'label' => Carbon::create($year, $m)->format('M'),
                'month' => $m,
                'deal_count' => (int) ($monthData->deal_count ?? 0),
                'total_value' => (float) ($monthData->total_value ?? 0),
            ];
        }

        // ── Per Sale Person Accordion ────────────────────────────
        $personData = DB::table('sales')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->leftJoin('teams', 'employees.team_id', '=', 'teams.id')
            ->where('users.role', '!=', 'agent')
            ->where('sales.status', 'transferred')
            ->whereYear('sales.created_at', $year)
            ->select(
                'users.id as user_id',
                'users.name',
                'teams.name as team_name',
                DB::raw('COUNT(sales.id) as deal_count'),
                DB::raw('SUM(listings.price_per_room) as total_value'),
                DB::raw('SUM(listings.reservation_deposit) as reservation_sum'),
                DB::raw('SUM(listings.contract_payment) as contract_sum'),
                DB::raw('SUM(listings.transfer_amount) as transfer_sum'),
            )
            ->groupBy('users.id', 'users.name', 'teams.name')
            ->orderByDesc('total_value')
            ->get();

        // Per-person deal list for accordion expand
        $personDeals = [];
        if ($personData->count()) {
            $userIds = $personData->pluck('user_id')->toArray();
            $deals = DB::table('sales')
                ->join('listings', 'sales.listing_id', '=', 'listings.id')
                ->where('sales.status', 'transferred')
                ->whereYear('sales.created_at', $year)
                ->whereIn('sales.user_id', $userIds)
                ->select(
                    'sales.user_id',
                    'sales.sale_number',
                    'listings.unit_code',
                    'listings.price_per_room',
                    'sales.created_at',
                )
                ->orderByDesc('sales.created_at')
                ->get();

            foreach ($deals as $d) {
                $personDeals[$d->user_id][] = $d;
            }
        }

        // ── Transfer Readiness ─────────────────────────────────
        $transferBase = DB::table('sales')
            ->whereIn('sales.status', ['installment', 'transferred'])
            ->whereYear('sales.created_at', $year);

        $transferStats = (object) [
            'total'      => (clone $transferBase)->count(),
            'approved'   => (clone $transferBase)->join('sale_transfers as st', 'sales.id', '=', 'st.sale_id')->where('st.transfer_readiness', 'approved')->count(),
            'on_process' => (clone $transferBase)->join('sale_transfers as st', 'sales.id', '=', 'st.sale_id')->where('st.transfer_readiness', 'on_process')->count(),
            'bank_loan'  => (clone $transferBase)->join('sale_transfers as st', 'sales.id', '=', 'st.sale_id')->where('st.transfer_payment_type', 'bank_loan')->count(),
            'cash'       => (clone $transferBase)->join('sale_transfers as st', 'sales.id', '=', 'st.sale_id')->where('st.transfer_payment_type', 'cash')->count(),
        ];

        $transferCustomers = DB::table('sales')
            ->join('listings', 'sales.listing_id', '=', 'listings.id')
            ->join('projects', 'listings.project_id', '=', 'projects.id')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->leftJoin('sale_transfers as st', 'sales.id', '=', 'st.sale_id')
            ->whereIn('sales.status', ['installment', 'transferred'])
            ->whereYear('sales.created_at', $year)
            ->select(
                'sales.id as sale_id',
                'sales.sale_number',
                'sales.status',
                DB::raw("TRIM(CONCAT(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(sales.reservation_data, '$.first_name')), ''), ' ', COALESCE(JSON_UNQUOTE(JSON_EXTRACT(sales.reservation_data, '$.last_name')), ''))) as customer_name"),
                'listings.unit_code',
                'listings.price_per_room',
                'projects.name as project_name',
                'projects.default_transfer_payment_type',
                'users.name as salesperson',
                DB::raw("COALESCE(st.transfer_payment_type, projects.default_transfer_payment_type) as transfer_payment_type"),
                'st.transfer_readiness',
                'st.bank_name',
                'st.bank_account_number',
                'st.loan_amount',
                'st.actual_loan_amount',
                'st.customer_extra_payment',
                'st.bank_approved_at',
            )
            ->orderByDesc('sales.created_at')
            ->get();

        return view('finance.index', compact(
            'kpis', 'yearlyChart', 'quarterChartLabels', 'quarterChartValues',
            'quarterMonths', 'personData', 'personDeals',
            'transferStats', 'transferCustomers',
            'year', 'quarter'
        ));
    }

    public function storeTransfer(Request $request, Sale $sale)
    {
        $request->validate([
            'transfer_payment_type'  => 'required|in:bank_loan,cash',
            'transfer_readiness'     => 'required|in:on_process,approved,not_ready',
            'bank_name'              => 'nullable|string|max:255',
            'bank_account_number'    => 'nullable|string|max:255',
            'loan_amount'            => 'nullable|numeric|min:0',
            'actual_loan_amount'     => 'nullable|numeric|min:0',
            'customer_extra_payment' => 'nullable|numeric|min:0',
            'bank_approved_at'       => 'nullable|date',
        ]);

        abort_unless(in_array($sale->status, ['installment', 'transferred']), 422, 'Sale must be in installment or transferred status.');

        $data = $request->only([
            'transfer_payment_type', 'transfer_readiness',
            'bank_name', 'bank_account_number', 'loan_amount',
            'actual_loan_amount', 'customer_extra_payment', 'bank_approved_at',
        ]);

        if ($data['transfer_payment_type'] === 'cash') {
            $data['bank_name'] = null;
            $data['bank_account_number'] = null;
            $data['loan_amount'] = null;
            $data['actual_loan_amount'] = null;
            $data['customer_extra_payment'] = null;
            $data['bank_approved_at'] = null;
        }

        SaleTransfer::updateOrCreate(
            ['sale_id' => $sale->id],
            $data
        );

        return response()->json(['success' => true, 'message' => 'Transfer details saved.']);
    }
}
