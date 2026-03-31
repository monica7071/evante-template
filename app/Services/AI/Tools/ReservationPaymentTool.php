<?php
namespace App\Services\AI\Tools;

use App\Models\Listing;
use App\Models\Sale;
use App\Scopes\OrganizationScope;
use App\Services\PromptPayService;
use Illuminate\Support\Facades\Cache;

class ReservationPaymentTool extends AbstractTool
{
    public function name(): string { return 'reservation_payment'; }

    public function description(): string
    {
        return 'สร้าง QR PromptPay สำหรับชำระค่าจองห้อง/ยูนิต '
            . 'Use when customer wants to pay reservation deposit. '
            . 'Requires unit_code. Returns QR image and amount.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'unit_code' => [
                    'type' => 'string',
                    'description' => 'Room/unit code e.g. "B136", "A201"',
                ],
                'sale_number' => [
                    'type' => 'string',
                    'description' => 'Sale number if already booked (e.g. SL-20260331-0054)',
                ],
                'session_token' => [
                    'type' => 'string',
                    'description' => 'Chat session token for slip tracking (injected automatically)',
                ],
            ],
            'required' => ['unit_code'],
        ];
    }

    public function execute(array $input, int $organizationId): array
    {
        $promptpayId = config('services.promptpay.id', '');
        if (empty($promptpayId)) {
            return $this->error('ยังไม่ได้ตั้งค่า PromptPay ID กรุณาติดต่อทีมงานเพื่อชำระค่าจองค่ะ', 'not_configured');
        }

        // Get listing
        $listing = Listing::withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organizationId)
            ->where('unit_code', $input['unit_code'])
            ->first();

        if (!$listing) {
            return $this->notFound("ไม่พบห้อง '{$input['unit_code']}'");
        }

        $amount = (float) ($listing->reservation_deposit ?? 0);
        if ($amount <= 0) {
            return $this->error("ไม่พบข้อมูลค่าจองสำหรับห้อง {$input['unit_code']}", 'no_deposit');
        }

        // Find or use provided sale_number
        $saleNumber = $input['sale_number'] ?? null;
        if (!$saleNumber) {
            $sale = Sale::withoutGlobalScope(OrganizationScope::class)
                ->where('listing_id', $listing->id)
                ->whereIn('status', ['appointment', 'available'])
                ->latest()
                ->first();
            $saleNumber = $sale?->sale_number ?? 'PENDING';
        }

        // Generate QR
        $service = new PromptPayService();
        $qrImage = $service->generateQrBase64($promptpayId, $amount);
        $payload = $service->generatePayload($promptpayId, $amount);

        // Cache slip-pending state per session (30 min)
        if (!empty($input['session_token'])) {
            Cache::put(
                'slip_pending:' . $input['session_token'],
                ['sale_number' => $saleNumber, 'unit_code' => $input['unit_code'], 'amount' => $amount],
                now()->addMinutes(30)
            );
        }

        return $this->success([
            'unit_code'    => $listing->unit_code,
            'room_number'  => $listing->room_number,
            'floor'        => $listing->floor,
            'amount'       => $amount,
            'sale_number'  => $saleNumber,
            'promptpay_id' => $promptpayId,
            'qr_payload'   => $payload,
            'qr_image'     => $qrImage,
        ], "QR สำหรับชำระค่าจอง {$listing->unit_code} จำนวน " . number_format($amount, 0) . " บาท");
    }
}
