<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Sale;
use App\Models\SalePurchaseAgreement;
use App\Models\SalePurchaseAgreementInstallment;
use App\Services\PromptPayService;
use App\Services\RoundRobinAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // POST /api/v1/bookings
    public function createBooking(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unit_code'      => 'required|string',
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
        ]);

        $listing = Listing::where('unit_code', $validated['unit_code'])->first();

        if (! $listing) {
            return response()->json(['success' => false, 'message' => "Unit '{$validated['unit_code']}' not found."], 404);
        }

        $sale = Sale::where('listing_id', $listing->id)
            ->whereIn('status', ['available', 'appointment'])
            ->first();

        if (! $sale) {
            return response()->json(['success' => false, 'message' => 'This unit is not available for booking.'], 422);
        }

        $previousStatus = $sale->status;
        $sale->update(['status' => 'reserved', 'previous_status' => $previousStatus]);

        $agent = RoundRobinAssignmentService::assignToSale($sale);

        $sale->statusHistories()->create([
            'status'          => 'reserved',
            'previous_status' => $previousStatus,
            'notes'           => "Booked via API by {$validated['customer_name']}",
            'user_id'         => $agent?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully.',
            'data'    => [
                'sale_number'   => $sale->sale_number,
                'unit_code'     => $listing->unit_code,
                'status'        => 'reserved',
                'assigned_to'   => $agent?->name,
                'customer_name' => $validated['customer_name'],
            ],
        ], 201);
    }

    // GET /api/v1/payments/schedule?sale_number=SL-...
    public function schedule(Request $request): JsonResponse
    {
        $request->validate(['sale_number' => 'required|string']);

        $sale = Sale::where('sale_number', $request->sale_number)->first();

        if (! $sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        $agreement = $sale->purchaseAgreement;

        if (! $agreement) {
            return response()->json(['success' => true, 'data' => null, 'message' => 'No payment schedule found for this sale.']);
        }

        $installments = $agreement->installments()
            ->orderBy('sequence')
            ->get()
            ->map(fn ($i) => [
                'sequence'     => $i->sequence,
                'amount'       => (float) $i->amount_number,
                'amount_text'  => $i->amount_text,
                'due_date'     => $i->due_date?->format('Y-m-d'),
                'proof_image'  => $i->proof_image ? asset('storage/' . $i->proof_image) : null,
                'is_paid'      => filled($i->proof_image),
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'sale_number'     => $sale->sale_number,
                'contract_number' => $agreement->contract_number,
                'buyer_name'      => $agreement->buyer_full_name,
                'total_price'     => (float) $agreement->total_price_number,
                'deposit'         => (float) $agreement->deposit_number,
                'total_term'      => $agreement->total_term,
                'installments'    => $installments,
            ],
        ]);
    }

    // POST /api/v1/payments/qr
    public function generateQr(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount'       => 'required|numeric|min:1',
            'sale_number'  => 'required|string',
            'promptpay_id' => 'nullable|string',
        ]);

        $promptpayId = $validated['promptpay_id']
            ?? config('services.promptpay.id', '');

        if (empty($promptpayId)) {
            return response()->json([
                'success' => false,
                'message' => 'PromptPay ID not configured. Set PROMPTPAY_ID in .env or pass promptpay_id in request.',
            ], 422);
        }

        $service = new PromptPayService();
        $amount  = (float) $validated['amount'];

        $payload    = $service->generatePayload($promptpayId, $amount);
        $qrBase64   = $service->generateQrBase64($promptpayId, $amount);

        return response()->json([
            'success' => true,
            'data'    => [
                'sale_number'  => $validated['sale_number'],
                'amount'       => $amount,
                'promptpay_id' => $promptpayId,
                'qr_payload'   => $payload,
                'qr_image'     => $qrBase64,   // data:image/png;base64,...
            ],
        ]);
    }

    // POST /api/v1/payments/omise
    public function omiseCharge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount'      => 'required|numeric|min:1',
            'sale_number' => 'required|string',
            'token'       => 'required|string',
        ]);

        // Placeholder — integrate Omise SDK here
        return response()->json([
            'success' => true,
            'data'    => [
                'sale_number' => $validated['sale_number'],
                'amount'      => (float) $validated['amount'],
                'charge_id'   => null,
                'status'      => 'pending_integration',
                'note'        => 'Omise integration not yet configured. Set OMISE_SECRET_KEY in .env.',
            ],
        ]);
    }

    // GET /api/v1/payments/status?sale_number=SL-...
    public function status(Request $request): JsonResponse
    {
        $request->validate(['sale_number' => 'required|string']);

        $sale = Sale::where('sale_number', $request->sale_number)
            ->with('purchaseAgreement.installments')
            ->first();

        if (! $sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        $agreement = $sale->purchaseAgreement;
        $installments = $agreement?->installments ?? collect();
        $totalPaid = $installments->filter(fn ($i) => filled($i->proof_image))->sum('amount_number');
        $totalDue = $installments->sum('amount_number');

        return response()->json([
            'success' => true,
            'data'    => [
                'sale_number'    => $sale->sale_number,
                'sale_status'    => $sale->status,
                'total_due'      => (float) $totalDue,
                'total_paid'     => (float) $totalPaid,
                'remaining'      => (float) ($totalDue - $totalPaid),
                'total_terms'    => $installments->count(),
                'paid_terms'     => $installments->filter(fn ($i) => filled($i->proof_image))->count(),
                'overdue_terms'  => $installments->filter(fn ($i) => ! filled($i->proof_image) && $i->due_date && $i->due_date->isPast())->count(),
            ],
        ]);
    }

    // GET /api/v1/payments/overdue?sale_number=SL-...
    public function overdue(Request $request): JsonResponse
    {
        $query = SalePurchaseAgreementInstallment::whereNull('proof_image')
            ->where('due_date', '<', now()->toDateString())
            ->with('agreement.sale.listing');

        if ($request->filled('sale_number')) {
            $query->whereHas('agreement.sale', fn ($q) => $q->where('sale_number', $request->sale_number));
        }

        $overdue = $query->orderBy('due_date')->limit(50)->get()->map(fn ($i) => [
            'sale_number' => $i->agreement?->sale?->sale_number,
            'unit_code'   => $i->agreement?->sale?->listing?->unit_code,
            'sequence'    => $i->sequence,
            'amount'      => (float) $i->amount_number,
            'due_date'    => $i->due_date?->format('Y-m-d'),
            'days_overdue'=> $i->due_date ? (int) now()->diffInDays($i->due_date) : 0,
        ]);

        return response()->json([
            'success' => true,
            'total'   => $overdue->count(),
            'data'    => $overdue,
        ]);
    }
}
