<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Sale;
use App\Models\SalePurchaseAgreement;

class ContractPreviewPageController extends Controller
{
    public function reservation(Sale $sale)
    {
        return $this->renderPreviewPage($sale, 'reservation_agreement', 'Reservation Agreement');
    }

    public function addendum(Sale $sale)
    {
        return $this->renderPreviewPage($sale, 'addendum_to_agreement', 'Addendum to Agreement');
    }

    public function purchaseAgreement(Sale $sale)
    {
        return $this->renderPurchasePreviewPage($sale);
    }

    public function dealSlip(Sale $sale)
    {
        return $this->renderInstallmentPreviewPage($sale, 'Deal Slip', 'contracts.deal-slip.preview', 'contracts.deal-slip.download');
    }

    public function overdueReminder1(Sale $sale)
    {
        return $this->renderInstallmentPreviewPage($sale, 'Overdue Installment Reminder (1st Notice)', 'contracts.overdue-reminder-1.preview', 'contracts.overdue-reminder-1.download');
    }

    public function overdueReminder2(Sale $sale)
    {
        return $this->renderInstallmentPreviewPage($sale, 'Overdue Installment Reminder (2nd Notice)', 'contracts.overdue-reminder-2.preview', 'contracts.overdue-reminder-2.download');
    }

    private function renderPurchasePreviewPage(Sale $sale)
    {
        $sale->load('listing.project');

        $agreement = SalePurchaseAgreement::where('sale_id', $sale->id)->latest()->first();

        if (!$agreement) {
            return redirect()->route('buy-sale.index', ['status' => 'contract'])
                ->with('error', 'ไม่พบข้อมูลสัญญาซื้อขาย กรุณา Save Contract ก่อน');
        }

        return view('contracts.previews.reservation', [
            'sale'        => $sale,
            'pageTitle'   => 'Agreement to Sell and Purchase',
            'pdfUrl'      => route('contracts.purchase-agreement.preview', ['sale' => $sale->id]),
            'downloadUrl' => route('contracts.purchase-agreement.download', ['sale' => $sale->id]),
        ]);
    }

    private function renderInstallmentPreviewPage(Sale $sale, string $pageTitle, string $previewRoute, string $downloadRoute)
    {
        $sale->load('listing.project');

        $agreement = SalePurchaseAgreement::where('sale_id', $sale->id)->latest()->first();

        if (!$agreement) {
            return redirect()->route('buy-sale.installments', ['sale' => $sale->id])
                ->with('error', 'ไม่พบข้อมูลสัญญา กรุณา Save Contract ก่อน');
        }

        return view('contracts.previews.reservation', [
            'sale'        => $sale,
            'pageTitle'   => $pageTitle,
            'pdfUrl'      => route($previewRoute, ['sale' => $sale->id]),
            'downloadUrl' => route($downloadRoute, ['sale' => $sale->id]),
        ]);
    }

    private function renderPreviewPage(Sale $sale, string $contractType, string $pageTitle)
    {
        $sale->load('listing.project');

        $reservation = Reservation::with('listing')
            ->where('listing_id', $sale->listing_id)
            ->latest()
            ->first();

        if (!$reservation) {
            return redirect()->route('buy-sale.index', ['status' => 'reserved'])
                ->with('error', 'ไม่พบข้อมูลการจองสำหรับยูนิตนี้');
        }

        $pdfRoute = match ($contractType) {
            'addendum_to_agreement' => route('contracts.addendum.preview', ['sale' => $sale->id]),
            default => route('contracts.reservation-agreement.preview', ['sale' => $sale->id]),
        };

        $downloadRoute = match ($contractType) {
            'addendum_to_agreement' => route('contracts.addendum.download', ['sale' => $sale->id]),
            default => route('contracts.reservation-agreement.download', ['sale' => $sale->id]),
        };

        return view('contracts.previews.reservation', [
            'sale' => $sale,
            'reservation' => $reservation,
            'pageTitle' => $pageTitle,
            'pdfUrl' => $pdfRoute,
            'downloadUrl' => $downloadRoute,
            'contractType' => $contractType,
        ]);
    }
}
