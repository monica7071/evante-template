<?php

namespace App\Services\AI\Tools;

use App\Models\Listing;
use App\Models\Sale;
use App\Scopes\OrganizationScope;
use Illuminate\Support\Facades\DB;

class AppointmentBookTool extends AbstractTool
{
    public function name(): string
    {
        return 'appointment_book';
    }

    public function description(): string
    {
        return 'จองนัดชมห้อง/ยูนิต ระบุชื่อลูกค้า เบอร์โทร วันที่ และเวลาที่ต้องการ '
            . 'Use this after the customer has decided on a unit and provided their name, phone, and preferred date/time. '
            . 'You MUST collect: listing_id, customer_name, customer_phone, appointment_date before calling this tool.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'listing_id' => [
                    'type'        => 'integer',
                    'description' => 'ID of the listing/unit to book an appointment for',
                ],
                'customer_name' => [
                    'type'        => 'string',
                    'description' => 'Full name of the customer',
                ],
                'customer_phone' => [
                    'type'        => 'string',
                    'description' => 'Customer phone number',
                ],
                'appointment_date' => [
                    'type'        => 'string',
                    'description' => 'Appointment date in YYYY-MM-DD format',
                ],
                'appointment_time' => [
                    'type'        => 'string',
                    'description' => 'Preferred appointment time, e.g. "10:00", "14:30" (optional)',
                ],
                'remark' => [
                    'type'        => 'string',
                    'description' => 'Any additional notes or special requests from the customer',
                ],
            ],
            'required' => ['listing_id', 'customer_name', 'customer_phone', 'appointment_date'],
        ];
    }

    public function execute(array $input, int $organizationId): array
    {
        $listing = Listing::withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organizationId)
            ->find($input['listing_id']);

        if (! $listing) {
            return $this->notFound("ไม่พบยูนิต ID {$input['listing_id']}");
        }

        $dateStr = $input['appointment_date'];
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            return $this->error('รูปแบบวันที่ไม่ถูกต้อง กรุณาใช้ YYYY-MM-DD', 'invalid_date');
        }

        try {
            $date = \Carbon\Carbon::createFromFormat('Y-m-d', $dateStr);
            if ($date->isPast() && ! $date->isToday()) {
                return $this->error('วันที่นัดหมายต้องเป็นวันในอนาคต', 'invalid_date');
            }
        } catch (\Exception) {
            return $this->error('วันที่ไม่ถูกต้อง', 'invalid_date');
        }

        DB::beginTransaction();
        try {
            $sale                     = new Sale();
            $sale->listing_id         = $listing->id;
            $sale->organization_id    = $organizationId;
            $sale->status             = 'appointment';
            $sale->appointment_date   = $date;
            $sale->appointment_time   = $input['appointment_time'] ?? null;
            $sale->appointment_name   = $input['customer_name'];
            $sale->appointment_phone  = $input['customer_phone'];
            $sale->remark_appointment = $input['remark'] ?? null;

            $today             = now()->format('Ymd');
            $count             = Sale::withoutGlobalScope(OrganizationScope::class)
                ->whereDate('created_at', today())->count() + 1;
            $sale->sale_number = 'SL-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $listing->status = 'appointment';
            $listing->save();
            $sale->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('เกิดข้อผิดพลาดในการบันทึกนัดหมาย: ' . $e->getMessage(), 'db_error');
        }

        return $this->success([
            'sale_number'      => $sale->sale_number,
            'listing_id'       => $listing->id,
            'unit_code'        => $listing->unit_code,
            'room_number'      => $listing->room_number,
            'customer_name'    => $input['customer_name'],
            'customer_phone'   => $input['customer_phone'],
            'appointment_date' => $dateStr,
            'appointment_time' => $input['appointment_time'] ?? null,
        ], "จองนัดชมห้องสำเร็จ เลขที่ {$sale->sale_number}");
    }
}
