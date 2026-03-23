<?php

namespace App\Http\Controllers;

use App\Models\PdfTemplate;
use App\Models\Sale;
use App\Models\SalePurchaseAgreement;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use setasign\Fpdi\Fpdi;

class InstallmentContractController extends Controller
{
    public function previewDealSlip(Sale $sale): StreamedResponse|RedirectResponse
    {
        return $this->streamDocument($sale, 'deal_slip', 'ยังไม่มี Deal Slip Template กรุณาอัปโหลดก่อน', 'deal-slip', false);
    }

    public function downloadDealSlip(Sale $sale): StreamedResponse|RedirectResponse
    {
        return $this->streamDocument($sale, 'deal_slip', 'ยังไม่มี Deal Slip Template กรุณาอัปโหลดก่อน', 'deal-slip', true);
    }

    public function previewOverdueReminder1(Sale $sale): StreamedResponse|RedirectResponse
    {
        return $this->streamDocument($sale, 'overdue_installment_reminder_1', 'ยังไม่มี Overdue Reminder (1st Notice) Template กรุณาอัปโหลดก่อน', 'overdue-reminder-1', false);
    }

    public function downloadOverdueReminder1(Sale $sale): StreamedResponse|RedirectResponse
    {
        return $this->streamDocument($sale, 'overdue_installment_reminder_1', 'ยังไม่มี Overdue Reminder (1st Notice) Template กรุณาอัปโหลดก่อน', 'overdue-reminder-1', true);
    }

    public function previewOverdueReminder2(Sale $sale): StreamedResponse|RedirectResponse
    {
        return $this->streamDocument($sale, 'overdue_installment_reminder_2', 'ยังไม่มี Overdue Reminder (2nd Notice) Template กรุณาอัปโหลดก่อน', 'overdue-reminder-2', false);
    }

    public function downloadOverdueReminder2(Sale $sale): StreamedResponse|RedirectResponse
    {
        return $this->streamDocument($sale, 'overdue_installment_reminder_2', 'ยังไม่มี Overdue Reminder (2nd Notice) Template กรุณาอัปโหลดก่อน', 'overdue-reminder-2', true);
    }

    private function streamDocument(Sale $sale, string $contractType, string $missingMsg, string $filenamePrefix, bool $asAttachment): StreamedResponse|RedirectResponse
    {
        $agreement = SalePurchaseAgreement::with('installments')
            ->where('sale_id', $sale->id)
            ->latest()
            ->first();

        if (!$agreement) {
            return redirect()->route('buy-sale.installments', ['sale' => $sale->id])
                ->with('error', 'ไม่พบข้อมูลสัญญาสำหรับยูนิตนี้');
        }

        $template = PdfTemplate::where('contract_type', $contractType)
            ->with('mappings')
            ->latest()
            ->first();

        if (!$template) {
            return redirect()->route('buy-sale.installments', ['sale' => $sale->id])
                ->with('error', $missingMsg);
        }

        $templatePath = storage_path('app/public/' . $template->file_path);
        if (!file_exists($templatePath)) {
            return redirect()->route('buy-sale.installments', ['sale' => $sale->id])
                ->with('error', 'Template file missing from storage.');
        }

        $contractData = $this->buildContractData($agreement);

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($templatePath);
        $groupedMappings = $template->mappings->groupBy('page_number');
        $fontSizePt = 10;

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tplId);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';

            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);

            if (!$groupedMappings->has($pageNo)) {
                continue;
            }

            $pdf->SetFont('Helvetica', '', $fontSizePt);
            $pdf->SetTextColor(0, 0, 0);

            foreach ($groupedMappings->get($pageNo) as $mapping) {
                $value = $contractData[$mapping->db_field] ?? null;
                if ($value === null || $value === '') {
                    continue;
                }

                $pdf->Text(
                    $this->pointsToMm((float) $mapping->x_position),
                    $this->pointsToMm((float) $mapping->y_position + $fontSizePt),
                    (string) $value
                );
            }
        }

        $filename = $filenamePrefix . '-' . $sale->id . '.pdf';
        $disposition = ($asAttachment ? 'attachment' : 'inline') . '; filename="' . $filename . '"';

        return response()->stream(function () use ($pdf) {
            echo $pdf->Output('S');
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition,
        ]);
    }

    private function buildContractData(SalePurchaseAgreement $agreement): array
    {
        $formatMoney = static fn ($v) => $v !== null ? number_format((float) $v, 2) : '';

        $data = [
            'contract_number'           => $agreement->contract_number,
            'contract_date'             => optional($agreement->contract_date)->format('Y-m-d'),
            'buyer_full_name'           => $agreement->buyer_full_name,
            'buyer_phone'               => $agreement->buyer_phone,
            'house_no'                  => $agreement->house_no,
            'village_no'                => $agreement->village_no,
            'street'                    => $agreement->street,
            'province'                  => $agreement->province,
            'district'                  => $agreement->district,
            'subdistrict'               => $agreement->subdistrict,
            'project_name'              => $agreement->project_name,
            'floor'                     => $agreement->floor,
            'room_number'               => $agreement->room_number,
            'unit_type'                 => $agreement->unit_type,
            'quantity'                  => $agreement->quantity,
            'price_per_sqm_number'      => $formatMoney($agreement->price_per_sqm_number),
            'area_sqm'                  => $agreement->area_sqm,
            'total_price_number'        => $formatMoney($agreement->total_price_number),
            'total_price_text'          => $agreement->total_price_text,
            'adjustment_number'         => $formatMoney($agreement->adjustment_number),
            'adjustment_text'           => $agreement->adjustment_text,
            'deposit_number'            => $formatMoney($agreement->deposit_number),
            'deposit_text'              => $agreement->deposit_text,
            'deposit_date'              => optional($agreement->deposit_date)->format('Y-m-d'),
            'contract_payment_number'   => $formatMoney($agreement->contract_payment_number),
            'contract_payment_text'     => $agreement->contract_payment_text,
            'contract_payment_date'     => optional($agreement->contract_payment_date)->format('Y-m-d'),
            'installment_total_number'  => $formatMoney($agreement->installment_total_number),
            'installment_total_text'    => $agreement->installment_total_text,
            'remaining_number'          => $formatMoney($agreement->remaining_number),
            'remaining_text'            => $agreement->remaining_text,
            'total_term'                => $agreement->total_term,
            'is_bank_loan'              => $agreement->is_bank_loan ? '✓' : '',
            'is_cash_transfer'          => $agreement->is_cash_transfer ? '✓' : '',
            'seller_name'               => $agreement->seller_name,
            'buyer_signature_name'      => $agreement->buyer_signature_name,
            'witness_one_name'          => $agreement->witness_one_name,
            'witness_two_name'          => $agreement->witness_two_name,
            'user_name'                 => auth()->user()?->name ?? '',
            'user_phone'                => auth()->user()?->phone ?? '',
        ];

        foreach (($agreement->installments ?? collect())->sortBy('sequence') as $installment) {
            $seq = $installment->sequence;
            $data["installment_{$seq}_amount_number"] = $formatMoney($installment->amount_number);
            $data["installment_{$seq}_amount_text"]   = $installment->amount_text;
            $data["installment_{$seq}_due_date"]      = optional($installment->due_date)->format('Y-m-d');
        }

        return $data;
    }

    private function pointsToMm(float $points): float
    {
        return $points * 25.4 / 72;
    }
}
