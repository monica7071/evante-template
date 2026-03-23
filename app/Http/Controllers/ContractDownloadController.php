<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\PdfTemplate;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use setasign\Fpdi\Fpdi;

class ContractDownloadController extends Controller
{
    public function download(Contract $contract): StreamedResponse|RedirectResponse
    {
        $template = PdfTemplate::where('contract_type', $contract->type)
            ->with('mappings')
            ->latest()
            ->first();

        if (!$template) {
            return redirect()->route('dashboard')
                ->with('error', 'No PDF template found for this contract type. Please upload a template first.');
        }

        $templatePath = storage_path('app/public/' . $template->file_path);
        if (!file_exists($templatePath)) {
            return redirect()->route('contracts.preview', $contract)
                ->with('error', 'Template file missing from storage.');
        }

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($templatePath);
        $groupedMappings = $template->mappings->groupBy('page_number');
        $contractData = $this->contractData($contract);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tplId);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';

            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);

            if (!$groupedMappings->has($pageNo)) {
                continue;
            }

            $fontSizePt = 10;
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

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->Output('S');
        }, 'contract-' . $contract->id . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function contractData(Contract $contract): array
    {
        return [
            'buyer_name' => $contract->buyer_name,
            'id_number' => $contract->id_number,
            'phone' => $contract->phone,
            'email' => $contract->email,
            'unit_number' => $contract->unit_number,
            'price' => number_format((float) $contract->price, 2),
            'deposit' => $contract->deposit !== null ? number_format((float) $contract->deposit, 2) : '',
            'contract_date' => optional($contract->contract_date)->format('Y-m-d'),
            'image_path' => $contract->image_path ? storage_path('app/public/' . $contract->image_path) : null,
        ];
    }

    private function pointsToMm(float $points): float
    {
        return $points * 25.4 / 72;
    }
}
