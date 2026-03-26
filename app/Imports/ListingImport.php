<?php

namespace App\Imports;

use App\Models\Listing;
use App\Models\Location;
use App\Models\Project;
use App\Models\ProjectImage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ListingImport implements ToModel, WithHeadingRow
{
    public array $rowErrors = [];
    public int   $imported  = 0;
    public int   $skipped   = 0;

    private array $locationCache = [];
    private array $projectCache  = [];

    public function model(array $row): ?Listing
    {
        // Skip completely empty rows
        if (empty(array_filter($row))) {
            return null;
        }

        $rowNum = $this->imported + $this->skipped + 2;

        // Resolve location
        $locationName = trim($row['location'] ?? '');
        $location     = $this->findLocation($locationName);
        if (!$location) {
            $this->rowErrors[] = "Row {$rowNum}: Location '{$locationName}' not found.";
            $this->skipped++;
            return null;
        }

        // Resolve project
        $projectName = trim($row['project_name'] ?? '');
        $project     = $this->findProject($projectName);
        if (!$project) {
            $this->rowErrors[] = "Row {$rowNum}: Project '{$projectName}' not found.";
            $this->skipped++;
            return null;
        }

        // room_number is required
        $roomNumber = trim($row['room_number'] ?? '');
        if ($roomNumber === '') {
            $this->rowErrors[] = "Row {$rowNum}: room_number is required.";
            $this->skipped++;
            return null;
        }

        // Generate unit_code if not provided: {BuildingLetter}{floor}{room padded 2}
        // e.g. building=A, floor=1, room=1 → A101
        $unitCode = trim($row['unit_code'] ?? '');
        if ($unitCode === '') {
            $buildingLetter = strtoupper(substr($project->name, 0, 1));
            $floorPart      = $this->num($row['floor'] ?? null) ?? 0;
            $roomPart       = str_pad(preg_replace('/\D/', '', $roomNumber), 2, '0', STR_PAD_LEFT);
            $unitCode       = $buildingLetter . $floorPart . $roomPart;

            // If still collides, append a suffix
            $base = $unitCode;
            $i    = 2;
            while (Listing::where('unit_code', $unitCode)->exists()) {
                $unitCode = $base . '-' . $i++;
            }
        } elseif (Listing::where('unit_code', $unitCode)->exists()) {
            $this->rowErrors[] = "Row {$rowNum}: unit_code '{$unitCode}' already exists — skipped.";
            $this->skipped++;
            return null;
        }

        $validStatuses = ['available', 'reserved', 'contract', 'installment', 'transferred'];
        $status        = trim(strtolower($row['status'] ?? 'available'));
        if (!in_array($status, $validStatuses)) {
            $status = 'available';
        }

        $this->imported++;

        // Auto-assign floor plan image from project_images table
        $floor = $this->num($row['floor'] ?? null);
        $floorPlanImage = null;
        if ($floor) {
            $floorPlanImage = ProjectImage::where('type', 'floor_plan')
                ->where('project_id', $project->id)
                ->where('floor', $floor)
                ->value('image_path');
        }

        // Auto-assign room layout image from project_images table (shared)
        $unitType = trim($row['unit_type'] ?? '');
        $roomLayoutImage = null;
        if ($unitType) {
            $roomLayoutImage = ProjectImage::where('type', 'room_layout')
                ->where('unit_type', strtoupper($unitType))
                ->value('image_path');
        }

        $price       = $this->decimal($row['price_per_room'] ?? null);
        $area        = $this->decimal($row['area'] ?? null);
        $utilityFee  = $this->decimal($row['utility_fee'] ?? null) ?? 0;

        // Auto-calculate financial fields if not provided (same formula as the web form)
        $calc = $this->calcFinancials($price, $area, $utilityFee);

        return new Listing([
            'location_id'                => $location->id,
            'project_id'                 => $project->id,
            'building'                   => $project->name,
            'project_name'               => $location->project_name,
            'floor'                      => $floor,
            'room_number'                => $roomNumber,
            'unit_code'                  => $unitCode ?: null,
            'bedrooms'                   => trim($row['bedrooms'] ?? '') ?: null,
            'area'                       => $area,
            'price_per_room'             => $price,
            'price_per_sqm'              => $this->decimal($row['price_per_sqm'] ?? null) ?? $calc['price_per_sqm'],
            'unit_type'                  => $unitType ?: null,
            'status'                     => $status,
            'reservation_deposit'        => $this->decimal($row['reservation_deposit'] ?? null) ?? $calc['reservation_deposit'],
            'contract_payment'           => $this->decimal($row['contract_payment'] ?? null) ?? $calc['contract_payment'],
            'installment_15_terms'       => $this->decimal($row['installment_15_terms'] ?? null) ?? $calc['installment_15_terms'],
            'installment_15_terms_en'    => $this->decimal($row['installment_15_terms_en'] ?? null) ?? $calc['installment_15_terms_en'],
            'installment_12_terms'       => $this->decimal($row['installment_12_terms'] ?? null) ?? $calc['installment_12_terms'],
            'special_installment_3_terms'=> $this->decimal($row['special_installment_3_terms'] ?? null) ?? $calc['special_installment_3_terms'],
            'transfer_amount'            => $this->decimal($row['transfer_amount'] ?? null) ?? $calc['transfer_amount'],
            'transfer_amount_en'         => $this->decimal($row['transfer_amount_en'] ?? null) ?? $calc['transfer_amount_en'],
            'transfer_fee'               => $this->decimal($row['transfer_fee'] ?? null) ?? $calc['transfer_fee'],
            'annual_common_fee'          => $this->decimal($row['annual_common_fee'] ?? null) ?? $calc['annual_common_fee'],
            'sinking_fund'               => $this->decimal($row['sinking_fund'] ?? null) ?? $calc['sinking_fund'],
            'utility_fee'                => $utilityFee ?: null,
            'total_misc_fee'             => $this->decimal($row['total_misc_fee'] ?? null) ?? $calc['total_misc_fee'],
            'floor_plan_image'           => $floorPlanImage,
            'room_layout_image'          => $roomLayoutImage,
        ]);
    }

    private function findLocation(string $name): ?Location
    {
        // Normalize: collapse multiple spaces, trim
        $normalized = strtolower(preg_replace('/\s+/', ' ', trim($name)));
        if (!isset($this->locationCache[$normalized])) {
            $this->locationCache[$normalized] = Location::all()
                ->first(fn ($l) => strtolower(preg_replace('/\s+/', ' ', trim($l->project_name))) === $normalized);
        }
        return $this->locationCache[$normalized];
    }

    private function findProject(string $name): ?Project
    {
        $normalized = strtolower(preg_replace('/\s+/', ' ', trim($name)));
        if (!isset($this->projectCache[$normalized])) {
            $this->projectCache[$normalized] = Project::all()
                ->first(fn ($p) => strtolower(preg_replace('/\s+/', ' ', trim($p->name))) === $normalized);
        }
        return $this->projectCache[$normalized];
    }

    private function num(mixed $value): ?int
    {
        return ($value !== null && $value !== '') ? (int) $value : null;
    }

    private function calcFinancials(?float $price, ?float $area, float $utility): array
    {
        if (!$price) {
            return array_fill_keys([
                'price_per_sqm','reservation_deposit','contract_payment',
                'installment_15_terms','installment_15_terms_en','installment_12_terms','special_installment_3_terms',
                'transfer_amount','transfer_amount_en','transfer_fee','annual_common_fee','sinking_fund','total_misc_fee',
            ], null);
        }

        $pricePerSqm  = ($area > 0) ? round($price / $area)          : null;
        $reservation  = (int) ceil($price * 0.0025);
        $contractPay  = (int) ceil($price * 0.0275);
        $inst15       = round($price * 0.095);
        $inst15En     = round($price * 0.27);
        $inst12       = ($inst15 > 0) ? round(0.8 * ($inst15 / 15))  : null;
        $special3     = ($inst15 > 0 && $inst12) ? round(($inst15 - ($inst12 * 12)) / 3) : null;
        $transferAmt  = round($price * 0.875);
        $transferAmtEn = round($price * 0.70);
        $transferFee  = (int) ceil(($price * 0.02) / 2);
        $annualCommon = ($area > 0) ? round(55 * $area * 12)          : null;
        $sinkingFund  = ($area > 0) ? round($area * 650)              : null;
        $totalMisc    = $transferFee + ($annualCommon ?? 0) + ($sinkingFund ?? 0) + $utility;

        return [
            'price_per_sqm'               => $pricePerSqm,
            'reservation_deposit'         => $reservation,
            'contract_payment'            => $contractPay,
            'installment_15_terms'        => $inst15,
            'installment_15_terms_en'     => $inst15En,
            'installment_12_terms'        => $inst12,
            'special_installment_3_terms' => $special3,
            'transfer_amount'             => $transferAmt,
            'transfer_amount_en'          => $transferAmtEn,
            'transfer_fee'                => $transferFee,
            'annual_common_fee'           => $annualCommon,
            'sinking_fund'                => $sinkingFund,
            'total_misc_fee'              => $totalMisc,
        ];
    }

    private function decimal(mixed $value): ?float
    {
        if ($value === null || $value === '') return null;
        return (float) str_replace(',', '', (string) $value);
    }
}
