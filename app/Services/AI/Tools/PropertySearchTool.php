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
            . 'Also use when customer wants to see a specific room by listing_id.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'listing_id' => [
                    'type'        => 'integer',
                    'description' => 'Specific listing ID to retrieve details for a single unit',
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
                    'type'        => 'integer',
                    'description' => 'Number of bedrooms (0 = studio)',
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
                    'description' => 'Unit type string, e.g. "1BR", "2BR", "Studio"',
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
        // Single room detail by listing_id
        if (isset($input['listing_id'])) {
            $listing = Listing::withoutGlobalScope(OrganizationScope::class)
                ->where('organization_id', $organizationId)
                ->with(['project:id,name'])
                ->find($input['listing_id']);

            if (! $listing) {
                return $this->notFound("ไม่พบยูนิต ID {$input['listing_id']}");
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
                'floor_plan_image' => $listing->floor_plan_image,
                'room_layout_image' => $listing->room_layout_image,
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
            $query->where('bedrooms', $input['bedrooms']);
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
            'floor_plan_image'  => $l->floor_plan_image,
            'room_layout_image' => $l->room_layout_image,
        ])->values()->all();

        return $this->success($items, "พบ {$listings->count()} ยูนิต");
    }
}
