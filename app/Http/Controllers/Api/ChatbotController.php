<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Project;
use App\Models\Promotion;
use App\Models\Sale;
use App\Models\StatusHistory;
use App\Services\RoundRobinAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    // GET /api/v1/projects
    public function projects(): JsonResponse
    {
        $projects = Project::with('location')
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'project_id'   => $p->id,
                'name'         => $p->name,
                'location'     => $p->location->name ?? null,
                'total_floors' => $p->total_floors,
                'total_units'  => $p->total_units,
            ]);

        return response()->json([
            'success' => true,
            'total'   => $projects->count(),
            'data'    => $projects,
        ]);
    }

    // GET /api/v1/rooms
    public function availableRooms(Request $request): JsonResponse
    {
        // Show listings that are available:
        // 1. Has a sale record with status 'available' (and no other active sale), OR
        // 2. Has NO sale record at all (never been sold/reserved)
        $query = Listing::with('project')
            ->where(function ($q) {
                $q->where(function ($sub) {
                    // Has an 'available' sale and no other active status
                    $sub->whereHas('sales', fn ($s) => $s->where('status', 'available'))
                        ->whereDoesntHave('sales', fn ($s) => $s->whereNotIn('status', ['available', 'transferred']));
                })->orWhereDoesntHave('sales'); // No sale record = available
            });

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('floor')) {
            $query->where('floor', $request->floor);
        }

        if ($request->filled('unit_type')) {
            $term = $request->unit_type;
            $query->where(function ($q) use ($term) {
                $q->where('unit_type', $term)
                  ->orWhere('bedrooms', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('bedrooms')) {
            $term = $request->bedrooms;
            $query->where(function ($q) use ($term) {
                $q->where('bedrooms', $term)
                  ->orWhere('bedrooms', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('room_number')) {
            $term = $request->room_number;
            $query->where(function ($q) use ($term) {
                $q->where('room_number', $term)
                  ->orWhere('unit_code', $term)
                  ->orWhere('room_number', 'like', '%' . $term . '%')
                  ->orWhere('unit_code', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('unit_code')) {
            $query->where('unit_code', $request->unit_code);
        }

        if ($request->filled('keyword')) {
            $term = $request->keyword;
            $query->where(function ($q) use ($term) {
                $q->where('unit_code', 'like', '%' . $term . '%')
                  ->orWhere('room_number', 'like', '%' . $term . '%')
                  ->orWhere('unit_type', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_room', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_room', '<=', $request->max_price);
        }

        $limit    = min((int) ($request->input('limit', 20)), 50);
        $listings = $query->orderBy('floor')->orderBy('room_number')->limit($limit)->get();

        $data = $listings->map(fn ($l) => $this->formatRoom($l));

        return response()->json([
            'success' => true,
            'total'   => $data->count(),
            'data'    => $data,
        ]);
    }

    // GET /api/v1/rooms/{unit_code}
    public function roomDetail(string $unitCode): JsonResponse
    {
        $listing = Listing::with('project')
            ->where('unit_code', $unitCode)
            ->first();

        if (!$listing) {
            return response()->json([
                'success' => false,
                'message' => "Room '{$unitCode}' not found.",
            ], 404);
        }

        $sale = Sale::where('listing_id', $listing->id)->latest()->first();

        $appointmentInfo = null;
        if ($sale?->status === 'appointment' && $sale->appointment) {
            $appointmentInfo = [
                'date'  => $sale->appointment->appointment_date?->format('Y-m-d'),
                'time'  => $sale->appointment->appointment_time ? substr($sale->appointment->appointment_time, 0, 5) : null,
                'remark' => $sale->appointment->remark,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => array_merge(
                $this->formatRoom($listing),
                [
                    'status'           => $sale?->status ?? 'no_sale_record',
                    'appointment'      => $appointmentInfo,
                    'reservation_deposit' => $listing->reservation_deposit ? (float) $listing->reservation_deposit : null,
                    'contract_payment'    => $listing->contract_payment ? (float) $listing->contract_payment : null,
                    'floor_plan_image'    => $listing->floor_plan_image
                        ? asset('storage/' . $listing->floor_plan_image)
                        : null,
                    'room_layout_image'   => $listing->room_layout_image
                        ? asset('storage/' . $listing->room_layout_image)
                        : null,
                    'financial' => [
                        'installment_15_terms'      => $listing->installment_15_terms ? (float) $listing->installment_15_terms : null,
                        'installment_12_terms'      => $listing->installment_12_terms ? (float) $listing->installment_12_terms : null,
                        'special_installment_3_terms' => $listing->special_installment_3_terms ? (float) $listing->special_installment_3_terms : null,
                        'transfer_amount'           => $listing->transfer_amount ? (float) $listing->transfer_amount : null,
                        'annual_common_fee'         => $listing->annual_common_fee ? (float) $listing->annual_common_fee : null,
                        'sinking_fund'              => $listing->sinking_fund ? (float) $listing->sinking_fund : null,
                    ],
                ]
            ),
        ]);
    }

    // POST /api/v1/appointments
    public function bookAppointment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unit_code'        => 'required|string',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'visitor_name'     => 'required|string|max:255',
            'visitor_phone'    => 'required|string|max:50',
            'visitor_email'    => 'nullable|email|max:255',
        ]);

        $listing = Listing::where('unit_code', $validated['unit_code'])->first();

        if (!$listing) {
            return response()->json([
                'success' => false,
                'message' => "Room '{$validated['unit_code']}' not found.",
            ], 404);
        }

        $sale = Sale::where('listing_id', $listing->id)
            ->where('status', 'available')
            ->first();

        if (!$sale) {
            return response()->json([
                'success' => false,
                'message' => 'This room is not available for appointment.',
            ], 422);
        }

        $previousStatus = $sale->status;

        $sale->update([
            'status'           => 'appointment',
            'previous_status'  => $previousStatus,
        ]);

        $sale->appointment()->updateOrCreate(
            ['sale_id' => $sale->id],
            [
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $validated['appointment_time'] . ':00',
                'remark' => $validated['visitor_name'] . ' / ' . $validated['visitor_phone'],
            ]
        );

        // Round-robin assign sales agent
        $assignedAgent = RoundRobinAssignmentService::assignToSale($sale);

        $sale->statusHistories()->create([
            'status'          => 'appointment',
            'previous_status' => $previousStatus,
            'notes'           => 'Booked via chatbot by ' . $validated['visitor_name'],
            'user_id'         => $assignedAgent?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully.',
            'data'    => [
                'unit_code'        => $listing->unit_code,
                'project'          => $listing->project->name ?? null,
                'floor'            => $listing->floor,
                'room_number'      => $listing->room_number,
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $validated['appointment_time'],
                'visitor_name'     => $validated['visitor_name'],
                'visitor_phone'    => $validated['visitor_phone'],
                'visitor_email'    => $validated['visitor_email'] ?? null,
                'assigned_to'     => $assignedAgent?->name,
            ],
        ], 201);
    }

    // POST /api/v1/appointments/{unit_code}/cancel
    public function cancelAppointment(string $unitCode): JsonResponse
    {
        $listing = Listing::where('unit_code', $unitCode)->first();

        if (!$listing) {
            return response()->json([
                'success' => false,
                'message' => "Room '{$unitCode}' not found.",
            ], 404);
        }

        $sale = Sale::where('listing_id', $listing->id)
            ->where('status', 'appointment')
            ->first();

        if (!$sale) {
            return response()->json([
                'success' => false,
                'message' => 'No active appointment found for this room.',
            ], 422);
        }

        $sale->appointment?->delete();

        $sale->update([
            'status'           => 'available',
            'previous_status'  => 'appointment',
        ]);

        $sale->statusHistories()->create([
            'status'          => 'available',
            'previous_status' => 'appointment',
            'notes'           => 'Appointment cancelled via chatbot',
            'user_id'         => null,
        ]);

        Listing::where('id', $listing->id)->update(['status' => 'available']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled. Room is now available.',
            'data'    => [
                'unit_code' => $listing->unit_code,
                'status'    => 'available',
            ],
        ]);
    }

    private function formatRoom(Listing $listing): array
    {
        // Per-listing images take priority; fall back to project_images lookup
        $floorPlanImage  = $listing->floor_plan_image
            ? asset('storage/' . $listing->floor_plan_image)
            : $this->resolveProjectImage('floor_plan', $listing->project_id, $listing->floor, null);

        $roomLayoutImage = $listing->room_layout_image
            ? asset('storage/' . $listing->room_layout_image)
            : $this->resolveProjectImage('room_layout', $listing->project_id, null, $listing->unit_type);

        return [
            'listing_id'        => $listing->id,
            'unit_code'         => $listing->unit_code,
            'project'           => $listing->project->name ?? null,
            'building'          => $listing->building,
            'floor'             => $listing->floor,
            'room_number'       => $listing->room_number,
            'unit_type'         => $listing->unit_type,
            'area'              => $listing->area ? (float) $listing->area : null,
            'price'             => $listing->price_per_room ? (float) $listing->price_per_room : null,
            'price_per_sqm'     => $listing->price_per_sqm ? (float) $listing->price_per_sqm : null,
            'bedrooms'          => $listing->bedrooms,
            'floor_plan_image'  => $floorPlanImage,
            'room_layout_image' => $roomLayoutImage,
        ];
    }

    private function resolveProjectImage(string $type, ?int $projectId, ?int $floor, ?string $unitType): ?string
    {
        $query = \App\Models\ProjectImage::where('type', $type);

        if ($type === 'floor_plan' && $projectId && $floor !== null) {
            $query->where('project_id', $projectId)->where('floor', $floor);
        } elseif ($type === 'room_layout' && $unitType) {
            $query->where('unit_type', strtoupper($unitType));
        } else {
            return null;
        }

        $img = $query->first();
        return $img ? asset('storage/' . $img->image_path) : null;
    }

    // GET /api/v1/units/{unit_code}/price
    public function unitPrice(string $unitCode): JsonResponse
    {
        $listing = Listing::where('unit_code', $unitCode)->first();

        if (! $listing) {
            return response()->json(['success' => false, 'message' => "Unit '{$unitCode}' not found."], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'unit_code'              => $listing->unit_code,
                'price_per_room'         => $listing->price_per_room ? (float) $listing->price_per_room : null,
                'price_per_sqm'          => $listing->price_per_sqm ? (float) $listing->price_per_sqm : null,
                'reservation_deposit'    => $listing->reservation_deposit ? (float) $listing->reservation_deposit : null,
                'contract_payment'       => $listing->contract_payment ? (float) $listing->contract_payment : null,
                'installment_15_terms'   => $listing->installment_15_terms ? (float) $listing->installment_15_terms : null,
                'installment_12_terms'   => $listing->installment_12_terms ? (float) $listing->installment_12_terms : null,
                'special_installment_3_terms' => $listing->special_installment_3_terms ? (float) $listing->special_installment_3_terms : null,
                'transfer_amount'        => $listing->transfer_amount ? (float) $listing->transfer_amount : null,
                'transfer_fee'           => $listing->transfer_fee ? (float) $listing->transfer_fee : null,
                'annual_common_fee'      => $listing->annual_common_fee ? (float) $listing->annual_common_fee : null,
                'sinking_fund'           => $listing->sinking_fund ? (float) $listing->sinking_fund : null,
                'utility_fee'            => $listing->utility_fee ? (float) $listing->utility_fee : null,
                'total_misc_fee'         => $listing->total_misc_fee ? (float) $listing->total_misc_fee : null,
            ],
        ]);
    }

    // GET /api/v1/promotions
    public function promotions(): JsonResponse
    {
        $promotions = Promotion::active()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($p) => [
                'id'             => $p->id,
                'title'          => $p->title,
                'description'    => $p->description,
                'discount_type'  => $p->discount_type,
                'discount_value' => $p->discount_value,
                'start_date'     => $p->start_date?->format('Y-m-d'),
                'end_date'       => $p->end_date?->format('Y-m-d'),
                'conditions'     => $p->conditions,
            ]);

        return response()->json([
            'success' => true,
            'total'   => $promotions->count(),
            'data'    => $promotions,
        ]);
    }
}
