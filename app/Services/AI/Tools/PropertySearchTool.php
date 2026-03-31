<?php

namespace App\Services\AI\Tools;

use App\Models\Listing;
use App\Scopes\OrganizationScope;

class PropertySearchTool extends AbstractTool
{
    public function name(): string
    {
        return 'property_search';
    }

    public function description(): string
    {
        return 'ค้นหาห้อง/ยูนิตในโครงการตามเงื่อนไขที่ลูกค้าระบุ เช่น ราคา จำนวนห้องนอน พื้นที่ใช้สอย สถานะ '
            . 'Use this tool when the customer asks about available units, rooms, prices, or room details. '
            . 'Use room_number to find a specific room like "B231" or "A449". '
            . 'Also use when customer wants to see a specific room by listing_id.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'listing_id' => [
                    'type'        => 'string',
                    'description' => 'Specific listing ID (integer) or room code like "B231" to retrieve a single unit',
                ],
                'room_number' => [
                    'type'        => 'string',
                    'description' => 'Room number or unit code to search, e.g. "B231", "A449"',
                ],
                'keyword' => [
                    'type'        => 'string',
                    'description' => 'Free-text keyword to search across unit_code, room_number, unit_type',
                ],
                'unit_code' => [
                    'type'        => 'string',
                    'description' => 'Room/unit code e.g. "B422", "A106", "B349". Use this when customer mentions a specific room.',
                ],
                'project_id' => [
                    'type'        => 'integer',
                    'description' => 'ID of the project to filter by (optional)',
                ],
                'status' => [
                    'type'        => 'string',
                    'enum'        => ['available', 'appointment', 'reserved', 'contract', 'installment', 'transferred'],
                    'description' => 'Filter by listing status. Use "available" for unsold/available units.',
                ],
                'min_price' => [
                    'type'        => 'number',
                    'description' => 'Minimum price in THB',
                ],
                'max_price' => [
                    'type'        => 'number',
                    'description' => 'Maximum price in THB',
                ],
                'bedrooms' => [
                    'type'        => 'string',
                    'description' => 'Room type text, e.g. "1 Bed Smart", "1 Bed Plus", "2 Beds Smart", "2 Beds Plus", "Studio", or just a number like "1" or "2"',
                ],
                'min_area' => [
                    'type'        => 'number',
                    'description' => 'Minimum floor area in sq.m.',
                ],
                'max_area' => [
                    'type'        => 'number',
                    'description' => 'Maximum floor area in sq.m.',
                ],
                'unit_type' => [
                    'type'        => 'string',
                    'description' => 'Unit type code (e.g. "A", "B", "C"). Do NOT put room names here, use bedrooms instead.',
                ],
                'limit' => [
                    'type'        => 'integer',
                    'description' => 'Maximum number of results to return (default 10, max 20)',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $input, int $organizationId): array
    {
        // unit_code is an alias for listing_id when a room code is given
        if (isset($input['unit_code']) && ! isset($input['listing_id'])) {
            $input['listing_id'] = $input['unit_code'];
        }

        // Single room detail by listing_id (integer ID or room code string)
        if (isset($input['listing_id'])) {
            $lookupValue = $input['listing_id'];
            $baseQuery   = Listing::withoutGlobalScope(OrganizationScope::class)
                ->where('organization_id', $organizationId)
                ->with(['project:id,name']);

            if (is_numeric($lookupValue)) {
                $listing = $baseQuery->find((int) $lookupValue);
            } else {
                $listing = (clone $baseQuery)->where('unit_code', $lookupValue)->first()
                    ?? (clone $baseQuery)->where('room_number', $lookupValue)->first()
                    ?? (clone $baseQuery)->where('unit_code', 'like', '%' . $lookupValue . '%')->first()
                    ?? (clone $baseQuery)->where('room_number', 'like', '%' . $lookupValue . '%')->first();
            }

            if (! $listing) {
                return $this->notFound("ไม่พบยูนิต '{$lookupValue}'");
            }

            return $this->success([
                'id'             => $listing->id,
                'project'        => $listing->project?->name ?? $listing->project_name,
                'unit_code'      => $listing->unit_code,
                'room_number'    => $listing->room_number,
                'floor'          => $listing->floor,
                'building'       => $listing->building,
                'bedrooms'       => $listing->bedrooms,
                'area_sqm'       => (float) $listing->area,
                'unit_type'      => $listing->unit_type,
                'price'          => (float) $listing->price_per_room,
                'price_per_sqm'  => (float) $listing->price_per_sqm,
                'status'         => $listing->status,
                'floor_plan_image' => $listing->floor_plan_image ? asset('storage/' . $listing->floor_plan_image) : null,
                'room_layout_image' => $listing->room_layout_image ? asset('storage/' . $listing->room_layout_image) : null,
            ]);
        }

        // Search listings
        $query = Listing::withoutGlobalScope(OrganizationScope::class)
            ->where('organization_id', $organizationId)
            ->with(['project:id,name']);

        if (isset($input['project_id'])) {
            $query->where('project_id', $input['project_id']);
        }

        if (isset($input['status'])) {
            $query->where('status', $input['status']);
        }

        if (isset($input['min_price'])) {
            $query->where('price_per_room', '>=', $input['min_price']);
        }

        if (isset($input['max_price'])) {
            $query->where('price_per_room', '<=', $input['max_price']);
        }

        if (isset($input['bedrooms'])) {
            $term = $input['bedrooms'];
            $query->where(function ($q) use ($term) {
                $q->where('bedrooms', $term)
                  ->orWhere('bedrooms', 'like', '%' . $term . '%');
            });
        }

        if (isset($input['min_area'])) {
            $query->where('area', '>=', $input['min_area']);
        }

        if (isset($input['max_area'])) {
            $query->where('area', '<=', $input['max_area']);
        }

        if (isset($input['unit_type'])) {
            $query->where('unit_type', 'like', '%' . $input['unit_type'] . '%');
        }

        if (isset($input['room_number'])) {
            $term = $input['room_number'];
            $query->where(function ($q) use ($term) {
                $q->where('room_number', $term)
                  ->orWhere('unit_code', $term)
                  ->orWhere('room_number', 'like', '%' . $term . '%')
                  ->orWhere('unit_code', 'like', '%' . $term . '%');
            });
        }

        if (isset($input['keyword'])) {
            $term = $input['keyword'];
            $query->where(function ($q) use ($term) {
                $q->where('unit_code', 'like', '%' . $term . '%')
                  ->orWhere('room_number', 'like', '%' . $term . '%')
                  ->orWhere('unit_type', 'like', '%' . $term . '%');
            });
        }

        $limit    = min((int) ($input['limit'] ?? 10), 20);
        $listings = $query->orderBy('price_per_room')->limit($limit)->get();

        if ($listings->isEmpty()) {
            return $this->notFound('ไม่พบห้องที่ตรงกับเงื่อนไขที่ระบุ');
        }

        $items = $listings->map(fn ($l) => [
            'id'                => $l->id,
            'project'           => $l->project?->name ?? $l->project_name,
            'unit_code'         => $l->unit_code,
            'room_number'       => $l->room_number,
            'floor'             => $l->floor,
            'building'          => $l->building,
            'bedrooms'          => $l->bedrooms,
            'area_sqm'          => (float) $l->area,
            'unit_type'         => $l->unit_type,
            'price'             => (float) $l->price_per_room,
            'price_per_sqm'     => (float) $l->price_per_sqm,
            'status'            => $l->status,
            'floor_plan_image'  => $l->floor_plan_image ? asset('storage/' . $l->floor_plan_image) : null,
            'room_layout_image' => $l->room_layout_image ? asset('storage/' . $l->room_layout_image) : null,
        ])->values()->all();

        return $this->success($items, "พบ {$listings->count()} ยูนิต");
    }
}
