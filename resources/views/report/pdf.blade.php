<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: sarabun, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            padding: 0.55in;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2A8B92;
            padding-bottom: 10px;
        }
        .header h1 { font-size: 20px; color: #2A8B92; margin-bottom: 2px; }
        .header .subtitle { font-size: 11px; color: #666; }

        .section { margin-bottom: 18px; page-break-inside: avoid; }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #2A8B92;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        th, td { padding: 5px 8px; text-align: left; border-bottom: 1px solid #eee; }
        th {
            background: #f5f5f5;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            color: #555;
            border-bottom: 2px solid #ddd;
        }
        td { font-size: 11px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .bar-track { background: #eee; height: 10px; border-radius: 3px; }
        .bar-fill { height: 10px; border-radius: 3px; }

        .summary-box {
            display: inline-block;
            width: 48%;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 8px;
        }
        .summary-box .value { font-size: 16px; font-weight: bold; }
        .summary-box .label { font-size: 10px; color: #888; }

        .rank-badge {
            display: inline-block;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            text-align: center;
            line-height: 18px;
            font-size: 9px;
            font-weight: bold;
            color: #fff;
        }
        .rank-1 { background: #f59e0b; }
        .rank-2 { background: #94a3b8; }
        .rank-3 { background: #ef4444; }
        .rank-default { background: #cbd5e1; color: #475569; }

        .page-break { page-break-before: always; }

        .category-title {
            font-size: 14px;
            font-weight: bold;
            color: #2A8B92;
            margin: 16px 0 10px;
            padding: 4px 0;
            border-bottom: 2px solid #2A8B92;
        }
    </style>
</head>
<body>
    @php
        $view = $filters['view'];
        $barColors = ['#2A8B92', '#7c3aed', '#f79009', '#0ba5ec', '#12b76a', '#dc3545'];
    @endphp

    {{-- Header --}}
    <div class="header">
        <h1>Sales Report</h1>
        <div class="subtitle">
            {{ $monthLabel }} &middot; {{ ucfirst($view) }}
            @if($filters['teamId']) &middot; Team Filtered @endif
        </div>
    </div>

    <div class="category-title">📊 Sales Performance</div>

    {{-- 1. Sale Value by Team --}}
    <div class="section">
        <div class="section-title">Sale Value by Team</div>
        @if($teamChart->count())
            <table>
                <thead>
                    <tr>
                        <th>Team</th>
                        <th class="text-right">Deals</th>
                        <th class="text-right">Value (฿)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teamChart as $row)
                        <tr>
                            <td>{{ $row->team_name }}</td>
                            <td class="text-right">{{ number_format($row->deal_count) }}</td>
                            <td class="text-right font-bold">{{ number_format($row->total_value, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr style="border-top: 2px solid #999;">
                        <td class="font-bold">Total</td>
                        <td class="text-right font-bold">{{ number_format($teamChart->sum('deal_count')) }}</td>
                        <td class="text-right font-bold">{{ number_format($teamChart->sum('total_value'), 0) }}</td>
                    </tr>
                </tbody>
            </table>
        @else
            <p style="color: #999;">No data</p>
        @endif
    </div>

    {{-- 2. Sale Value by Person --}}
    <div class="section">
        <div class="section-title">Sale Value by Person</div>
        @if($saleChart->count())
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Team</th>
                        <th class="text-right">Deals</th>
                        <th class="text-right">Value (฿)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($saleChart as $row)
                        <tr>
                            <td>{{ $row->name }}</td>
                            <td>{{ $row->team_name ?? '—' }}</td>
                            <td class="text-right">{{ number_format($row->deal_count) }}</td>
                            <td class="text-right font-bold">{{ number_format($row->total_value, 0) }}</td>
                        </tr>
                    @endforeach
                    <tr style="border-top: 2px solid #999;">
                        <td class="font-bold" colspan="2">Total</td>
                        <td class="text-right font-bold">{{ number_format($saleChart->sum('deal_count')) }}</td>
                        <td class="text-right font-bold">{{ number_format($saleChart->sum('total_value'), 0) }}</td>
                    </tr>
                </tbody>
            </table>
        @else
            <p style="color: #999;">No data</p>
        @endif
    </div>

    {{-- 3. Top 5 Sale Performance --}}
    <div class="section">
        <div class="section-title">Top 5 Sale Performance</div>
        @if($top5->count())
            <table>
                <thead>
                    <tr>
                        <th class="text-center" style="width: 30px;">#</th>
                        <th>Name</th>
                        <th>Team</th>
                        <th class="text-right">Value (฿)</th>
                        <th class="text-right">Deals</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($top5 as $i => $row)
                        <tr>
                            <td class="text-center">
                                <span class="rank-badge {{ $i < 3 ? 'rank-' . ($i + 1) : 'rank-default' }}">{{ $i + 1 }}</span>
                            </td>
                            <td class="font-bold">{{ $row->name }}</td>
                            <td>{{ $row->team_name ?? '—' }}</td>
                            <td class="text-right font-bold">{{ number_format($row->total_value, 0) }}</td>
                            <td class="text-right">{{ number_format($row->deal_count) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color: #999;">No data</p>
        @endif
    </div>

    {{-- 4. Unit Type Breakdown --}}
    <div class="section">
        <div class="section-title">Unit Type Breakdown</div>
        @if($unitTypeBar->count())
            <table>
                <thead>
                    <tr>
                        <th>Unit Type</th>
                        <th class="text-right">Count</th>
                        <th class="text-right">Value (฿)</th>
                        <th style="width: 30%;">Distribution</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unitTypeBar as $i => $row)
                        @php $pct = round($row->cnt / $unitTypeMax * 100); @endphp
                        <tr>
                            <td class="font-bold">{{ $row->unit_type }}</td>
                            <td class="text-right">{{ number_format($row->cnt) }}</td>
                            <td class="text-right">{{ number_format($row->val, 0) }}</td>
                            <td>
                                <div class="bar-track">
                                    <div class="bar-fill" style="width: {{ $pct }}%; background: {{ $barColors[$i % count($barColors)] }};"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color: #999;">No data</p>
        @endif
    </div>

    <div class="category-title">👥 Customer Analysis</div>

    {{-- 5a. Customer Nationality --}}
    <div class="section">
        <div class="section-title">Customer Nationality</div>
        @php
            $thaiNat = $nationalitySplit->get('Thai');
            $foreignNat = $nationalitySplit->get('Foreign');
            $thaiCount = $thaiNat->deal_count ?? 0;
            $thaiValue = $thaiNat->total_value ?? 0;
            $foreignCount = $foreignNat->deal_count ?? 0;
            $foreignValue = $foreignNat->total_value ?? 0;
            $natTotal = $thaiCount + $foreignCount;
        @endphp
        <table>
            <thead>
                <tr>
                    <th>Nationality</th>
                    <th class="text-right">Count</th>
                    <th class="text-right">Value (฿)</th>
                    <th class="text-right">%</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Thai (บัตรประชาชน)</td>
                    <td class="text-right">{{ number_format($thaiCount) }}</td>
                    <td class="text-right">{{ number_format($thaiValue, 0) }}</td>
                    <td class="text-right">{{ $natTotal > 0 ? round($thaiCount / $natTotal * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Foreign (Passport)</td>
                    <td class="text-right">{{ number_format($foreignCount) }}</td>
                    <td class="text-right">{{ number_format($foreignValue, 0) }}</td>
                    <td class="text-right">{{ $natTotal > 0 ? round($foreignCount / $natTotal * 100, 1) : 0 }}%</td>
                </tr>
                <tr style="border-top: 2px solid #999;">
                    <td class="font-bold">Total</td>
                    <td class="text-right font-bold">{{ number_format($natTotal) }}</td>
                    <td class="text-right font-bold">{{ number_format($thaiValue + $foreignValue, 0) }}</td>
                    <td class="text-right font-bold">100%</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- 5. Customer Type --}}
    <div class="section">
        <div class="section-title">Customer Type</div>
        @php
            $bl = $customerSplit->bank_loan_count ?? 0;
            $blVal = $customerSplit->bank_loan_value ?? 0;
            $cc = $customerSplit->cash_count ?? 0;
            $ccVal = $customerSplit->cash_value ?? 0;
            $total = $bl + $cc;
        @endphp
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th class="text-right">Count</th>
                    <th class="text-right">Value (฿)</th>
                    <th class="text-right">%</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Bank Loan</td>
                    <td class="text-right">{{ number_format($bl) }}</td>
                    <td class="text-right">{{ number_format($blVal, 0) }}</td>
                    <td class="text-right">{{ $total > 0 ? round($bl / $total * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Cash Transfer</td>
                    <td class="text-right">{{ number_format($cc) }}</td>
                    <td class="text-right">{{ number_format($ccVal, 0) }}</td>
                    <td class="text-right">{{ $total > 0 ? round($cc / $total * 100, 1) : 0 }}%</td>
                </tr>
                <tr style="border-top: 2px solid #999;">
                    <td class="font-bold">Total</td>
                    <td class="text-right font-bold">{{ number_format($total) }}</td>
                    <td class="text-right font-bold">{{ number_format($blVal + $ccVal, 0) }}</td>
                    <td class="text-right font-bold">100%</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- 5c. Payment Type by Nationality --}}
    <div class="section">
        <div class="section-title">Payment Type by Nationality</div>
        @php
            $payNatMap = [];
            foreach ($paymentByNationality as $row) {
                $payNatMap[$row->nationality][$row->payment_type] = $row;
            }
            $thaiBankCnt = $payNatMap['Thai']['Bank Loan']->cnt ?? 0;
            $thaiBankVal = $payNatMap['Thai']['Bank Loan']->val ?? 0;
            $thaiCashCnt = $payNatMap['Thai']['Cash Transfer']->cnt ?? 0;
            $thaiCashVal = $payNatMap['Thai']['Cash Transfer']->val ?? 0;
            $forBankCnt = $payNatMap['Foreign']['Bank Loan']->cnt ?? 0;
            $forBankVal = $payNatMap['Foreign']['Bank Loan']->val ?? 0;
            $forCashCnt = $payNatMap['Foreign']['Cash Transfer']->cnt ?? 0;
            $forCashVal = $payNatMap['Foreign']['Cash Transfer']->val ?? 0;
            $maxBar = max($thaiBankCnt + $thaiCashCnt, $forBankCnt + $forCashCnt, 1);
        @endphp

        {{-- Legend --}}
        <div style="margin-bottom: 8px; font-size: 10px;">
            <span style="display: inline-block; width: 10px; height: 10px; background: #2A8B92; border-radius: 2px; vertical-align: middle;"></span> Bank Loan
            &nbsp;&nbsp;
            <span style="display: inline-block; width: 10px; height: 10px; background: #f79009; border-radius: 2px; vertical-align: middle;"></span> Cash Transfer
        </div>

        @foreach([['Thai', $thaiBankCnt, $thaiBankVal, $thaiCashCnt, $thaiCashVal], ['Foreign', $forBankCnt, $forBankVal, $forCashCnt, $forCashVal]] as $row)
            @php
                $rowTotal = $row[1] + $row[3];
                $bankPct = $maxBar > 0 ? round($row[1] / $maxBar * 100) : 0;
                $cashPct = $maxBar > 0 ? round($row[3] / $maxBar * 100) : 0;
            @endphp
            <div style="margin-bottom: 10px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span style="font-weight: bold; font-size: 11px;">{{ $row[0] }}</span>
                    <span style="font-size: 10px; color: #666;">
                        Bank: {{ number_format($row[1]) }} (฿{{ number_format($row[2], 0) }})
                        &middot; Cash: {{ number_format($row[3]) }} (฿{{ number_format($row[4], 0) }})
                        &middot; Total: {{ number_format($rowTotal) }}
                    </span>
                </div>
                <div class="bar-track" style="height: 14px;">
                    <div style="display: flex; height: 100%;">
                        <div class="bar-fill" style="width: {{ $bankPct }}%; background: #2A8B92;"></div>
                        <div class="bar-fill" style="width: {{ $cashPct }}%; background: #f79009; border-radius: 0 3px 3px 0;"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="category-title">📈 Production</div>

    {{-- 6. Production (yearly) --}}
    <div class="section">
        <div class="section-title">Production ({{ $year }})</div>
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="text-right">Transferred Value (฿)</th>
                </tr>
            </thead>
            <tbody>
                @php $totalProd = 0; @endphp
                @for($i = 0; $i < 12; $i++)
                    @php $totalProd += $productionChart->values[$i]; @endphp
                    <tr>
                        <td>{{ $productionChart->labels[$i] }}</td>
                        <td class="text-right {{ $productionChart->values[$i] > 0 ? 'font-bold' : '' }}">
                            {{ number_format($productionChart->values[$i], 0) }}
                        </td>
                    </tr>
                @endfor
                <tr style="border-top: 2px solid #999;">
                    <td class="font-bold">Total</td>
                    <td class="text-right font-bold">{{ number_format($totalProd, 0) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
