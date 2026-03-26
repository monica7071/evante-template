<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\PdfTemplate;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use setasign\Fpdi\Tfpdf\Fpdi;

class QuotationContractController extends Controller
{
    public function index(): View
    {
        $listings = Listing::with(['project.location'])
            ->orderBy('project_id')
            ->orderBy('unit_code')
            ->get();

        return view('contracts.quotation', compact('listings'));
    }

    public function previewListing(Listing $listing, string $language): View|RedirectResponse
    {
        $listing->load(['project.location']);

        $template = PdfTemplate::where('contract_type', 'quotation')
            ->where('language', $language)
            ->with('mappings')
            ->latest()
            ->first();

        if (!$template) {
            return redirect()->route('contracts.quotation.index')
                ->with('error', 'ยังไม่มี Quotation Template สำหรับภาษา ' . strtoupper($language));
        }

        $contractData = $this->buildContractDataFromListing($listing, $language);

        return view('contracts.quotation-preview', [
            'listing' => $listing,
            'language' => $language,
            'template' => $template,
            'contractData' => $contractData,
        ]);
    }

    public function downloadListing(Listing $listing, string $language): StreamedResponse|RedirectResponse
    {
        $listing->load(['project.location']);

        $template = PdfTemplate::where('contract_type', 'quotation')
            ->where('language', $language)
            ->with('mappings')
            ->latest()
            ->first();

        if (!$template) {
            return redirect()->route('contracts.quotation.index')
                ->with('error', 'ยังไม่มี Quotation Template สำหรับภาษา ' . strtoupper($language));
        }

        $templatePath = storage_path('app/public/' . $template->file_path);
        if (!file_exists($templatePath)) {
            return redirect()->route('contracts.quotation.preview-listing', [$listing, $language])
                ->with('error', 'Template file missing from storage.');
        }

        if (!defined('_SYSTEM_TTFONTS')) {
            define('_SYSTEM_TTFONTS', storage_path('fonts') . '/');
        }
        $pdf = new Fpdi();
        $pdf->AddFont('Sarabun', '', 'Sarabun-Regular.ttf', true);
        $pdf->AddFont('Sarabun', 'B', 'Sarabun-Bold.ttf', true);

        $pageCount = $pdf->setSourceFile($templatePath);
        $groupedMappings = $template->mappings->groupBy('page_number');
        $contractData = $this->buildContractDataFromListing($listing, $language);

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
            $pdf->SetFont('Sarabun', '', $fontSizePt);
            $pdf->SetTextColor(0, 0, 0);

            foreach ($groupedMappings->get($pageNo) as $mapping) {
                $value = $contractData[$mapping->db_field] ?? null;
                if ($value === null || $value === '') {
                    continue;
                }

                $x = $this->pointsToMm((float) $mapping->x_position);
                $y = $this->pointsToMm((float) $mapping->y_position);

                if ($mapping->field_type === 'image') {
                    $localPath  = storage_path('app/public/' . ltrim($value, '/'));
                    $renderPath = $this->resolveImagePath($localPath);
                    if ($renderPath) {
                        $imgWidth = (float) ($mapping->img_width ?? 50);
                        // x, y are TOP-LEFT coords — place directly
                        $pdf->Image($renderPath, $x, $y, $imgWidth);
                        if ($renderPath !== $localPath) @unlink($renderPath);
                    }
                } else {
                    $pdf->Text($x, $y + $this->pointsToMm($fontSizePt), (string) $value);
                }
            }
        }

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->Output('S');
        }, 'quotation-' . $listing->id . '-' . $language . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function buildContractDataFromListing(Listing $listing, string $language = 'th'): array
    {
        $project = $listing->project;
        $location = $listing->location;

        $formatMoney = fn($value) => $value !== null ? number_format((float) $value, 2) : '';

        $buildingName = $listing->building ?: ($project?->name ?? null);

        // Use EN-specific fields when language is 'en', fall back to TH values
        $installment15 = ($language === 'en' && $listing->installment_15_terms_en !== null)
            ? $listing->installment_15_terms_en
            : $listing->installment_15_terms;
        $transferAmount = ($language === 'en' && $listing->transfer_amount_en !== null)
            ? $listing->transfer_amount_en
            : $listing->transfer_amount;

        return [
            'listing_building' => $buildingName,
            'listing_unit_code' => $listing->unit_code,
            'listing_room_number' => $listing->room_number,
            'listing_floor' => $listing->floor,
            'listing_bedrooms' => $listing->bedrooms,
            'listing_area' => $listing->area,
            'listing_price_per_room' => $formatMoney($listing->price_per_room),
            'listing_price_per_sqm' => $formatMoney($listing->price_per_sqm),
            'listing_unit_type' => ($listing->unit_type && $listing->unit_type !== '-') ? $listing->unit_type : '',
            'listing_project_name' => $project?->name,
            'listing_location_name' => $location?->project_name,
            'listing_location_province' => $location?->province,
            'listing_location_district' => $location?->district,
            'listing_reservation_deposit' => $formatMoney($listing->reservation_deposit),
            'listing_contract_payment' => $formatMoney($listing->contract_payment),
            'listing_installment_15_terms' => $formatMoney($installment15),
            'listing_installment_12_terms' => $formatMoney($listing->installment_12_terms),
            'listing_special_installment_3_terms' => $formatMoney($listing->special_installment_3_terms),
            'listing_transfer_amount' => $formatMoney($transferAmount),
            'listing_transfer_fee' => $formatMoney($listing->transfer_fee),
            'listing_annual_common_fee' => $formatMoney($listing->annual_common_fee),
            'listing_sinking_fund' => $formatMoney($listing->sinking_fund),
            'listing_utility_fee' => $formatMoney($listing->utility_fee),
            'listing_total_misc_fee' => $formatMoney($listing->total_misc_fee),
            'listing_floor_plan_image' => $listing->floor_plan_image ?: null,
            'listing_room_layout_image' => $listing->room_layout_image ?: null,
            'user_name' => auth()->user()?->name,
            'user_phone' => auth()->user()?->phone,
            'sale_avail_name' => Sale::where('listing_id', $listing->id)->latest()->value('avail_name'),
            'sale_avail_tel' => Sale::where('listing_id', $listing->id)->latest()->value('avail_tel'),
        ];
    }

    /**
     * Returns a FPDF-compatible image path.
     * Converts WebP to a temp JPEG if needed.
     */
    private function resolveImagePath(string $localPath): ?string
    {
        if (!file_exists($localPath)) {
            return null;
        }

        $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
        if ($ext !== 'webp') {
            return $localPath;
        }

        $image = imagecreatefromwebp($localPath);
        if (!$image) {
            return null;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'fpdf_') . '.jpg';
        imagejpeg($image, $tmp, 85);
        imagedestroy($image);

        return $tmp;
    }

    private function pointsToMm(float $points): float
    {
        return $points * 25.4 / 72;
    }
}
