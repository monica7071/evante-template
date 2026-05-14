<?php

namespace App\Services\AI\Tools;

use App\Models\Listing;
use App\Models\Sale;
use App\Scopes\OrganizationScope;
use App\Services\RoundRobinAssignmentService;
use Illuminate\Support\Facades\DB;

class AppointmentBookTool extends AbstractTool
{
    public function name(): string
    {
        return 'appointment_book';
    }

    public function description(): string
    {
        return 'จองนัดชมโครงการหรือห้อง/ยูนิต ระบุชื่อลูกค้า เบอร์โทร วันที่ และเวลาที่ต้องการ '
            . 'listing_id is OPTIONAL — if the customer wants to visit the project without choosing a specific unit, omit it. '
            . 'You MUST collect: customer_name, customer_phone, appointment_date before calling this tool.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'listing_id' => [
                    'type'        => ['integer', 'string'],
                    'description' => 'ID (integer) or unit_code (string like "ECT-1001") of the listing/unit to book. Omit if customer just wants to visit the project without a specific unit.',
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
            'required' => ['customer_name', 'customer_phone', 'appointment_date'],
        ];
    }

    public function execute(array $input, int $organizationId): array
    {
        // Validate date
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

        // Resolve listing (optional)
        $listing = null;
        $listingId = $input['listing_id'] ?? null;

        if ($listingId) {
            $query = Listing::withoutGlobalScope(OrganizationScope::class)
                ->where('organization_id', $organizationId);

            if (is_numeric($listingId)) {
                $listing = $query->find($listingId);
            } else {
                $listing = $query->where('unit_code', $listingId)->first();
            }

            if (! $listing) {
                return $this->notFound("ไม่พบยูนิต {$listingId}");
            }
        }

        DB::beginTransaction();
        try {
            $existingSale = null;

            if ($listing) {
                // Check for existing available sale on this listing
                $existingSale = Sale::withoutGlobalScope(OrganizationScope::class)
                    ->where('listing_id', $listing->id)
                    ->where('status', 'available')
                    ->first();
            }

            if ($existingSale) {
                $sale = $existingSale;
                $sale->previous_status = $sale->status;
                $sale->status = 'appointment';
                $sale->save();
            } else {
                $today = now()->format('Ymd');
                $count = Sale::withoutGlobalScope(OrganizationScope::class)
                    ->whereDate('created_at', today())->count() + 1;

                $sale = new Sale();
                $sale->listing_id      = $listing?->id;
                $sale->organization_id = $organizationId;
                $sale->status          = 'appointment';
                $sale->sale_number     = 'SL-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                $sale->save();
            }

            // Build remark: name / phone
            $remark = $input['customer_name'] . ' / ' . $input['customer_phone'];
            if (!empty($input['remark'])) {
                $remark .= ' — ' . $input['remark'];
            }

            $sale->appointment()->updateOrCreate(
                ['sale_id' => $sale->id],
                [
                    'appointment_date' => $date,
                    'appointment_time' => ($input['appointment_time'] ?? '10:00') . ':00',
                    'remark'           => $remark,
                ]
            );

            $sale->statusHistories()->create([
                'status'          => 'appointment',
                'previous_status' => $existingSale ? 'available' : null,
                'notes'           => 'Booked via chatbot by ' . $input['customer_name'],
                'user_id'         => null,
            ]);

            // Round-robin assign sales agent
            $assignedAgent = RoundRobinAssignmentService::assignToSale($sale);

            if ($listing) {
                $listing->status = 'appointment';
                $listing->save();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('เกิดข้อผิดพลาดในการบันทึกนัดหมาย: ' . $e->getMessage(), 'db_error');
        }

        $result = [
            'sale_number'      => $sale->sale_number,
            'assigned_to'      => $assignedAgent?->name,
            'customer_name'    => $input['customer_name'],
            'customer_phone'   => $input['customer_phone'],
            'appointment_date' => $dateStr,
            'appointment_time' => $input['appointment_time'] ?? null,
            'type'             => $listing ? 'unit_visit' : 'project_visit',
        ];

        if ($listing) {
            $result['listing_id']  = $listing->id;
            $result['unit_code']   = $listing->unit_code;
            $result['room_number'] = $listing->room_number;
        }

        $typeLabel = $listing ? "ห้อง {$listing->unit_code}" : 'โครงการ (ยังไม่ระบุห้อง)';

        return $this->success($result, "จองนัดชม{$typeLabel}สำเร็จ เลขที่ {$sale->sale_number}");
    }
}
