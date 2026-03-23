<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ListingTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function headings(): array
    {
        return [
            'location',
            'project_name',
            'floor',
            'room_number',
            'unit_code',
            'bedrooms',
            'area',
            'price_per_room',
            'price_per_sqm',
            'unit_type',
            'status',
            'reservation_deposit',
            'contract_payment',
            'installment_15_terms',
            'installment_12_terms',
            'special_installment_3_terms',
            'transfer_amount',
            'transfer_fee',
            'annual_common_fee',
            'sinking_fund',
            'utility_fee',
            'total_misc_fee',
        ];
    }

    public function array(): array
    {
        // One example row so users can see the format
        return [
            [
                'Sukhumvit Location',  // location
                'Sky Tower',           // project_name
                '5',                   // floor
                '501',                 // room_number
                'A501',                // unit_code
                '2',                   // bedrooms
                '45.50',               // area
                '3500000',             // price_per_room
                '76923',               // price_per_sqm
                'Condo',               // unit_type
                'available',           // status
                '50000',               // reservation_deposit
                '150000',              // contract_payment
                '25000',               // installment_15_terms
                '30000',               // installment_12_terms
                '90000',               // special_installment_3_terms
                '3000000',             // transfer_amount
                '50000',               // transfer_fee
                '18000',               // annual_common_fee
                '22750',               // sinking_fund
                '500',                 // utility_fee
                '5000',                // total_misc_fee
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E2630']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22, 'B' => 22, 'C' => 8,  'D' => 14, 'E' => 12,
            'F' => 10, 'G' => 10, 'H' => 16, 'I' => 16, 'J' => 12,
            'K' => 14, 'L' => 20, 'M' => 18, 'N' => 22, 'O' => 22,
            'P' => 28, 'Q' => 18, 'R' => 16, 'S' => 20, 'T' => 14,
            'U' => 14, 'V' => 16,
        ];
    }
}
