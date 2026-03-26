<?php

namespace App\Services\AI\Tools;

class FinancialCalculatorTool extends AbstractTool
{
    public function name(): string
    {
        return 'financial_calculator';
    }

    public function description(): string
    {
        return 'คำนวณสินเชื่อบ้าน/คอนโด: ยอดผ่อนรายเดือน ดอกเบี้ยรวม วงเงินกู้สูงสุดตามรายได้ '
            . 'Also provides a summary of fees (reservation deposit, contract payment, transfer fee, etc.) '
            . 'from a specific listing. Always use this tool when the customer asks about monthly payments or loan calculations.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'calculation_type' => [
                    'type'        => 'string',
                    'enum'        => ['monthly_payment', 'max_loan', 'fee_summary', 'full_breakdown'],
                    'description' => 'Type of calculation: '
                        . '"monthly_payment" = calculate monthly payment from loan amount; '
                        . '"max_loan" = calculate max loan from monthly income; '
                        . '"fee_summary" = show all fees from a listing; '
                        . '"full_breakdown" = complete payment schedule breakdown',
                ],
                'property_price' => [
                    'type'        => 'number',
                    'description' => 'Total property price in THB',
                ],
                'down_payment_percent' => [
                    'type'        => 'number',
                    'description' => 'Down payment as a percentage of price (e.g. 10 for 10%). Default 10.',
                ],
                'loan_amount' => [
                    'type'        => 'number',
                    'description' => 'Loan amount in THB (used when calculation_type is monthly_payment)',
                ],
                'interest_rate' => [
                    'type'        => 'number',
                    'description' => 'Annual interest rate as a percentage (e.g. 6.5). Default 6.5 for Thai market.',
                ],
                'loan_term_years' => [
                    'type'        => 'integer',
                    'description' => 'Loan term in years (e.g. 20, 25, 30). Default 30.',
                ],
                'monthly_income' => [
                    'type'        => 'number',
                    'description' => 'Monthly gross income in THB (required for max_loan calculation)',
                ],
                'debt_ratio' => [
                    'type'        => 'number',
                    'description' => 'Maximum debt-to-income ratio as a decimal (default 0.40 = 40%)',
                ],
                'listing_id' => [
                    'type'        => 'integer',
                    'description' => 'Listing ID to pull actual fee data (for fee_summary or full_breakdown)',
                ],
            ],
            'required' => ['calculation_type'],
        ];
    }

    public function execute(array $input, int $organizationId): array
    {
        return match ($input['calculation_type']) {
            'monthly_payment' => $this->calcMonthlyPayment($input),
            'max_loan'        => $this->calcMaxLoan($input),
            'fee_summary'     => $this->calcFeeSummary($input, $organizationId),
            'full_breakdown'  => $this->calcFullBreakdown($input, $organizationId),
            default           => $this->error('ประเภทการคำนวณไม่รองรับ', 'invalid_type'),
        };
    }

    private function calcMonthlyPayment(array $input): array
    {
        $price      = (float) ($input['property_price'] ?? 0);
        $downPct    = (float) ($input['down_payment_percent'] ?? 10) / 100;
        $loanAmount = isset($input['loan_amount'])
            ? (float) $input['loan_amount']
            : $price * (1 - $downPct);
        $annualRate = (float) ($input['interest_rate'] ?? 6.5);
        $termYears  = (int) ($input['loan_term_years'] ?? 30);

        if ($loanAmount <= 0) {
            return $this->error('กรุณาระบุ loan_amount หรือ property_price', 'missing_param');
        }

        $monthly = $this->pmt($annualRate / 100 / 12, $termYears * 12, $loanAmount);

        return $this->success([
            'loan_amount'         => round($loanAmount, 2),
            'interest_rate_pct'   => $annualRate,
            'term_years'          => $termYears,
            'monthly_payment_thb' => round($monthly, 2),
            'total_payment_thb'   => round($monthly * $termYears * 12, 2),
            'total_interest_thb'  => round($monthly * $termYears * 12 - $loanAmount, 2),
            'down_payment_thb'    => $price > 0 ? round($price * $downPct, 2) : null,
        ]);
    }

    private function calcMaxLoan(array $input): array
    {
        $monthlyIncome = (float) ($input['monthly_income'] ?? 0);
        $debtRatio     = (float) ($input['debt_ratio'] ?? 0.40);
        $annualRate    = (float) ($input['interest_rate'] ?? 6.5);
        $termYears     = (int) ($input['loan_term_years'] ?? 30);

        if ($monthlyIncome <= 0) {
            return $this->error('กรุณาระบุ monthly_income', 'missing_param');
        }

        $maxPayment = $monthlyIncome * $debtRatio;
        $maxLoan    = $this->pv($annualRate / 100 / 12, $termYears * 12, $maxPayment);

        return $this->success([
            'monthly_income_thb'  => $monthlyIncome,
            'debt_ratio_pct'      => $debtRatio * 100,
            'max_monthly_payment' => round($maxPayment, 2),
            'max_loan_amount_thb' => round($maxLoan, 2),
            'interest_rate_pct'   => $annualRate,
            'term_years'          => $termYears,
        ]);
    }

    private function calcFeeSummary(array $input, int $organizationId): array
    {
        if (empty($input['listing_id'])) {
            return $this->error('กรุณาระบุ listing_id', 'missing_param');
        }

        $listing = \App\Models\Listing::withoutGlobalScope(\App\Scopes\OrganizationScope::class)
            ->where('organization_id', $organizationId)
            ->find($input['listing_id']);

        if (! $listing) {
            return $this->notFound("ไม่พบยูนิต ID {$input['listing_id']}");
        }

        return $this->success([
            'unit_code'            => $listing->unit_code,
            'price_per_room'       => (float) $listing->price_per_room,
            'reservation_deposit'  => (float) $listing->reservation_deposit,
            'contract_payment'     => (float) $listing->contract_payment,
            'transfer_amount'      => (float) $listing->transfer_amount,
            'transfer_fee'         => (float) $listing->transfer_fee,
            'installment_15_terms' => (float) $listing->installment_15_terms,
            'installment_12_terms' => (float) $listing->installment_12_terms,
            'annual_common_fee'    => (float) $listing->annual_common_fee,
            'sinking_fund'         => (float) $listing->sinking_fund,
            'utility_fee'          => (float) $listing->utility_fee,
            'total_misc_fee'       => (float) $listing->total_misc_fee,
        ]);
    }

    private function calcFullBreakdown(array $input, int $organizationId): array
    {
        $fees = $this->calcFeeSummary($input, $organizationId);
        if ($fees['status'] === 'error') {
            return $fees;
        }

        $price      = $fees['data']['price_per_room'];
        $downPct    = (float) ($input['down_payment_percent'] ?? 10) / 100;
        $annualRate = (float) ($input['interest_rate'] ?? 6.5);
        $termYears  = (int) ($input['loan_term_years'] ?? 30);

        $downPayment = $price * $downPct;
        $loanAmount  = $price - $downPayment;
        $monthly     = $this->pmt($annualRate / 100 / 12, $termYears * 12, $loanAmount);

        return $this->success(array_merge($fees['data'], [
            'down_payment_thb'    => round($downPayment, 2),
            'loan_amount_thb'     => round($loanAmount, 2),
            'monthly_payment_thb' => round($monthly, 2),
            'total_payment_thb'   => round($monthly * $termYears * 12, 2),
            'interest_rate_pct'   => $annualRate,
            'term_years'          => $termYears,
        ]));
    }

    private function pmt(float $rate, int $nper, float $pv): float
    {
        if ($rate === 0.0) {
            return $pv / $nper;
        }

        return $pv * $rate * (1 + $rate) ** $nper / ((1 + $rate) ** $nper - 1);
    }

    private function pv(float $rate, int $nper, float $pmt): float
    {
        if ($rate === 0.0) {
            return $pmt * $nper;
        }

        return $pmt * (1 - (1 + $rate) ** -$nper) / $rate;
    }
}
