<?php

namespace App\Http\Controllers;

use App\Models\PdfTemplate;
use App\Models\Reservation;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use setasign\Fpdi\Tfpdf\Fpdi;

class ReservationAgreementContractController extends Controller
{
    public function preview(Sale $sale): StreamedResponse|RedirectResponse
    {
        return $this->streamReservationDocument(
            $sale,
            'reservation_agreement',
            'ยังไม่มี Reservation Agreement Template กรุณาอัปโหลดก่อน',
            'reservation-agreement',
            asAttachment: false
        );
    }

    public function download(Sale $sale): StreamedResponse|RedirectResponse
    {
        return $this->streamReservationDocument(
            $sale,
            'reservation_agreement',
            'ยังไม่มี Reservation Agreement Template กรุณาอัปโหลดก่อน',
            'reservation-agreement'
        );
    }

    public function previewAddendum(Sale $sale): StreamedResponse|RedirectResponse
    {
        return $this->streamReservationDocument(
            $sale,
            'addendum_to_agreement',
            'ยังไม่มี Addendum Template กรุณาอัปโหลดก่อน',
            'reservation-addendum',
            asAttachment: false
        );
    }

    public function downloadAddendum(Sale $sale): StreamedResponse|RedirectResponse
    {
        return $this->streamReservationDocument(
            $sale,
            'addendum_to_agreement',
            'ยังไม่มี Addendum Template กรุณาอัปโหลดก่อน',
            'reservation-addendum'
        );
    }

    private function streamReservationDocument(
        Sale $sale,
        string $contractType,
        string $missingTemplateMessage,
        string $filenamePrefix,
        bool $asAttachment = true
    ): StreamedResponse|RedirectResponse {
        $reservation = Reservation::with('listing')
            ->where('listing_id', $sale->listing_id)
            ->latest()
            ->first();

        if (!$reservation) {
            return redirect()->route('buy-sale.index', ['status' => 'reserved'])
                ->with('error', 'ไม่พบข้อมูลการจองสำหรับยูนิตนี้');
        }

        $template = PdfTemplate::where('contract_type', $contractType)
            ->with('mappings')
            ->latest()
            ->first();

        if (!$template) {
            return redirect()->route('buy-sale.index', ['status' => 'reserved'])
                ->with('error', $missingTemplateMessage);
        }

        $templatePath = storage_path('app/public/' . $template->file_path);
        if (!file_exists($templatePath)) {
            return redirect()->route('buy-sale.index', ['status' => 'reserved'])
                ->with('error', 'Template file missing from storage.');
        }

        $contractData = $this->buildContractData($reservation);

        if (!defined('_SYSTEM_TTFONTS')) {
            define('_SYSTEM_TTFONTS', storage_path('fonts') . '/');
        }
        $pdf = new Fpdi();
        $pdf->AddFont('Sarabun', '', 'Sarabun-Regular.ttf', true);
        $pdf->AddFont('Sarabun', 'B', 'Sarabun-Bold.ttf', true);

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
                        $pdf->Image($renderPath, $x, $y, $imgWidth);
                        if ($renderPath !== $localPath) @unlink($renderPath);
                    }
                } else {
                    $pdf->Text($x, $y + $this->pointsToMm($fontSizePt), (string) $value);
                }
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

    private function buildContractData(Reservation $reservation): array
    {
        $formatMoney = static fn($value) => $value !== null ? number_format((float) $value, 2) : '';
        $storageUrl = static fn($path) => $path ? ltrim($path, '/') : null;

        $fullName = $reservation->buyer_full_name
            ?: trim(($reservation->buyer_first_name ?? '') . ' ' . ($reservation->buyer_last_name ?? ''));

        return [
            'reservation_buyer_first_name' => $reservation->buyer_first_name,
            'reservation_buyer_last_name' => $reservation->buyer_last_name,
            'reservation_buyer_full_name' => $fullName,
            'reservation_buyer_id_number' => $reservation->buyer_id_number,
            'reservation_buyer_address' => $reservation->buyer_address,
            'reservation_buyer_phone' => $reservation->buyer_phone,
            'reservation_buyer_email' => $reservation->buyer_email,
            'reservation_date' => optional($reservation->reservation_date)->format('Y-m-d'),
            'reservation_amount' => $formatMoney($reservation->reservation_amount),
            'reservation_amount_paid_number' => $formatMoney($reservation->amount_paid_number),
            'reservation_amount_paid_text' => $reservation->amount_paid_text,
            'reservation_contract_start_date' => optional($reservation->contract_start_date)->format('Y-m-d'),
            'reservation_buyer_signature_name' => $reservation->buyer_signature_name,
            'reservation_buyer_signature_path' => $storageUrl($reservation->buyer_signature_path),
            'reservation_seller_name' => $reservation->seller_name,
            'reservation_seller_signature_path' => $storageUrl($reservation->seller_signature_path),
            'reservation_witness_one_name' => $reservation->witness_one_name,
            'reservation_witness_one_signature_path' => $storageUrl($reservation->witness_one_signature_path),
            'reservation_witness_two_name' => $reservation->witness_two_name,
            'reservation_witness_two_signature_path' => $storageUrl($reservation->witness_two_signature_path),
        ];
    }

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
