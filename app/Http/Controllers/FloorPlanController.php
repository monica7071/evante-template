<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Sale;
use App\Models\SalePurchaseAgreement;
use Illuminate\Http\JsonResponse;

class FloorPlanController extends Controller
{
    public function index()
    {
        return view('floor-plan.index');
    }

    public function apiIndex(): JsonResponse
    {
        $listings = Listing::select('unit_code', 'status')
            ->with('latestStatusHistory')
            ->get();

        $units = [];
        foreach ($listings as $listing) {
            if ($listing->unit_code) {
                $units[$listing->unit_code] = $this->transformStatus($this->resolveStatus($listing));
            }
        }

        return response()->json([
            'units'    => $units,
            'statuses' => array_values(array_unique(array_values($units))),
        ]);
    }

    public function apiUnit(string $unitCode): JsonResponse
    {
        $listing = Listing::with('latestStatusHistory')->where('unit_code', $unitCode)->first();

        if (!$listing) {
            return response()->json(['error' => 'Unit not found'], 404);
        }

        // Pull the latest purchase agreement for this listing (if any)
        $agreement = SalePurchaseAgreement::where('listing_id', $listing->id)
            ->latest()
            ->first();

        $customerType = null;
        if ($agreement) {
            if ($agreement->is_bank_loan) {
                $customerType = 'Bank Loan';
            } elseif ($agreement->is_cash_transfer) {
                $customerType = 'Cash Transfer';
            }
        }

        // Find the related sale for quotation feature
        $sale = Sale::where('listing_id', $listing->id)->latest()->first();

        return response()->json([
            'unit_code'          => $listing->unit_code,
            'project_name'       => $listing->project_name ?? $unitCode,
            'process_status'     => $this->transformStatus($this->resolveStatus($listing)),
            'unit_type'          => $listing->unit_type,
            'bedrooms'           => $listing->bedrooms,
            'approximate_area'   => $listing->area,
            'price'              => $listing->price_per_room
                ? number_format((float) $listing->price_per_room, 2)
                : null,
            'price_per_sqm'      => $listing->price_per_sqm
                ? number_format((float) $listing->price_per_sqm, 2)
                : null,
            'room_layout_image'  => $listing->room_layout_image,
            'listing_id'         => $listing->id,
            'sale_id'            => $sale?->id,
            'avail_name'         => $sale?->avail_name,
            'avail_tel'          => $sale?->avail_tel,
            // Contract details — null when not yet filled
            'installment_count'  => $agreement?->total_term ?: null,
            'installment_total'  => $agreement?->installment_total_number
                ? number_format((float) $agreement->installment_total_number, 2)
                : null,
            'customer_type'      => $customerType,
        ]);
    }

    protected function transformStatus(?string $status): string
    {
        return match ($status) {
            'reserved'    => 'Reserved',
            'contract'    => 'Contract',
            'installment' => 'Installment',
            'transferred' => 'Transferred',
            default       => 'Available',
        };
    }

    private function resolveStatus(Listing $listing): ?string
    {
        return $listing->latestStatusHistory?->status ?? $listing->status;
    }
}
