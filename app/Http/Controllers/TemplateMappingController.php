<?php

namespace App\Http\Controllers;

use App\Models\PdfTemplate;
use App\Models\ProjectImage;
use App\Models\TemplateMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateMappingController extends Controller
{
    public function index()
    {
        $templates = PdfTemplate::withCount('mappings')->latest()->get();

        return view('templates.index', compact('templates'));
    }

    public function show(PdfTemplate $template)
    {
        $template->load('mappings');

        $fieldMeta = $this->fieldMetaFor($template->contract_type);
        $fieldLabels = $this->fieldLabelsFor($template->contract_type, $fieldMeta);
        $sampleData = $this->sampleDataFor($template->contract_type);

        $dbFields = array_keys($fieldMeta);

        return view('templates.mappings', compact('template', 'dbFields', 'fieldMeta', 'sampleData', 'fieldLabels'));
    }

    private function fieldMetaFor(string $contractType): array
    {
        if ($contractType === 'quotation') {
            return $this->listingFieldMeta();
        }

        if (in_array($contractType, ['reservation_agreement', 'addendum_to_agreement'], true)) {
            return $this->reservationFieldMeta();
        }

        if (in_array($contractType, ['deal_slip', 'overdue_installment_reminder_1', 'overdue_installment_reminder_2'], true)) {
            return $this->installmentFieldMeta();
        }

        return [
            'buyer_name' => 'text',
            'id_number' => 'number',
            'phone' => 'text',
            'email' => 'text',
            'unit_number' => 'text',
            'price' => 'number',
            'deposit' => 'number',
            'contract_date' => 'date',
            'image_path' => 'image',
        ];
    }

    private function listingFieldMeta(): array
    {
        return [
            'listing_building' => 'text',
            'listing_unit_code' => 'text',
            'listing_room_number' => 'text',
            'listing_floor' => 'text',
            'listing_bedrooms' => 'number',
            'listing_area' => 'number',
            'listing_price_per_room' => 'number',
            'listing_price_per_sqm' => 'number',
            'listing_unit_type' => 'text',
            'listing_location_name' => 'text',
            'listing_location_province' => 'text',
            'listing_location_district' => 'text',
            'listing_reservation_deposit' => 'number',
            'listing_contract_payment' => 'number',
            'listing_installment_15_terms' => 'number',
            'listing_installment_12_terms' => 'number',
            'listing_special_installment_3_terms' => 'number',
            'listing_transfer_amount' => 'number',
            'listing_transfer_fee' => 'number',
            'listing_annual_common_fee' => 'number',
            'listing_sinking_fund' => 'number',
            'listing_utility_fee' => 'number',
            'listing_total_misc_fee' => 'number',
            'listing_status' => 'text',
            'listing_floor_plan_image' => 'image',
            'listing_room_layout_image' => 'image',
            'user_name' => 'text',
            'user_phone' => 'text',
        ];
    }

    private function reservationFieldMeta(): array
    {
        return [
            'reservation_buyer_first_name' => 'text',
            'reservation_buyer_last_name' => 'text',
            'reservation_buyer_full_name' => 'text',
            'reservation_buyer_id_number' => 'text',
            'reservation_buyer_address' => 'text',
            'reservation_buyer_phone' => 'text',
            'reservation_buyer_email' => 'text',
            'reservation_date' => 'date',
            'reservation_amount' => 'number',
            'reservation_amount_paid_number' => 'number',
            'reservation_amount_paid_text' => 'text',
            'reservation_contract_start_date' => 'date',
            'reservation_buyer_signature_name' => 'text',
            'reservation_buyer_signature_path' => 'image',
            'reservation_seller_name' => 'text',
            'reservation_seller_signature_path' => 'image',
            'reservation_witness_one_name' => 'text',
            'reservation_witness_one_signature_path' => 'image',
            'reservation_witness_two_name' => 'text',
            'reservation_witness_two_signature_path' => 'image',
        ];
    }

    private function sampleDataFor(string $contractType): array
    {
        if ($contractType === 'quotation') {
            return $this->listingSampleData();
        }

        if (in_array($contractType, ['reservation_agreement', 'addendum_to_agreement'], true)) {
            return $this->reservationSampleData();
        }

        if (in_array($contractType, ['deal_slip', 'overdue_installment_reminder_1', 'overdue_installment_reminder_2'], true)) {
            return $this->installmentSampleData();
        }

        return [
            'buyer_name' => 'John Doe',
            'id_number' => '1234567890123',
            'phone' => '081-234-5678',
            'email' => 'john@example.com',
            'unit_number' => 'A-1201',
            'price' => '3,500,000',
            'deposit' => '100,000',
            'contract_date' => now()->format('Y-m-d'),
            'image_path' => 'https://via.placeholder.com/120x80.png?text=Image',
        ];
    }

    private function listingSampleData(): array
    {
        return [
            'listing_building' => 'A',
            'listing_unit_code' => 'A101',
            'listing_room_number' => '01',
            'listing_floor' => '1',
            'listing_bedrooms' => '1',
            'listing_area' => '35.50',
            'listing_price_per_room' => '3,850,000',
            'listing_price_per_sqm' => '108,000',
            'listing_unit_type' => 'A',
            'listing_location_name' => 'EVANTE  Samui Condominium',
            'listing_location_province' => 'Surat Thani',
            'listing_location_district' => 'Samui',
            'listing_reservation_deposit' => '50,000',
            'listing_contract_payment' => '100,000',
            'listing_installment_15_terms' => '365,000',
            'listing_installment_12_terms' => '292,000',
            'listing_special_installment_3_terms' => '219,000',
            'listing_transfer_amount' => '3,368,000',
            'listing_transfer_fee' => '38,500',
            'listing_annual_common_fee' => '23,100',
            'listing_sinking_fund' => '23,075',
            'listing_utility_fee' => '10,000',
            'listing_total_misc_fee' => '94,675',
            'listing_status' => 'available',
            'listing_floor_plan_image' => ProjectImage::where('type', 'floor_plan')->value('image_path'),
            'listing_room_layout_image' => ProjectImage::where('type', 'room_layout')->value('image_path'),
            'user_name' => auth()->user()?->name ?? 'Sales Name',
            'user_phone' => auth()->user()?->phone ?? '081-234-5678',
        ];
    }

    private function reservationSampleData(): array
    {
        return [
            'reservation_buyer_first_name' => 'Somchai',
            'reservation_buyer_last_name' => 'Sukjai',
            'reservation_buyer_full_name' => 'Somchai Sukjai',
            'reservation_buyer_id_number' => '1100200456789',
            'reservation_buyer_address' => '123 Sukhumvit Rd, Bangkok',
            'reservation_buyer_phone' => '089-555-0000',
            'reservation_buyer_email' => 'somchai@example.com',
            'reservation_date' => now()->format('Y-m-d'),
            'reservation_amount' => '50,000',
            'reservation_amount_paid_number' => '50,000',
            'reservation_amount_paid_text' => 'Fifty Thousand Baht Only',
            'reservation_contract_start_date' => now()->addDays(7)->format('Y-m-d'),
            'reservation_buyer_signature_name' => 'Somchai Sukjai',
            'reservation_buyer_signature_path' => 'https://via.placeholder.com/120x80.png?text=Buyer+Sign',
            'reservation_seller_name' => 'Evante Developments',
            'reservation_seller_signature_path' => 'https://via.placeholder.com/120x80.png?text=Seller+Sign',
            'reservation_witness_one_name' => 'Witness One',
            'reservation_witness_one_signature_path' => 'https://via.placeholder.com/120x80.png?text=W1+Sign',
            'reservation_witness_two_name' => 'Witness Two',
            'reservation_witness_two_signature_path' => 'https://via.placeholder.com/120x80.png?text=W2+Sign',
        ];
    }

    private function fieldLabelsFor(string $contractType, array $fieldMeta): array
    {
        $defaults = [
            'buyer_name' => 'Buyer Name',
            'id_number' => 'ID Number',
            'phone' => 'Phone Number',
            'email' => 'Email',
            'unit_number' => 'Unit Number',
            'price' => 'Price',
            'deposit' => 'Deposit',
            'contract_date' => 'Contract Date',
            'image_path' => 'Image',
        ];

        if ($contractType === 'quotation') {
            return array_intersect_key($this->listingFieldLabels(), $fieldMeta);
        }

        if (in_array($contractType, ['reservation_agreement', 'addendum_to_agreement'], true)) {
            return array_intersect_key($this->reservationFieldLabels(), $fieldMeta);
        }

        if (in_array($contractType, ['deal_slip', 'overdue_installment_reminder_1', 'overdue_installment_reminder_2'], true)) {
            return array_intersect_key($this->installmentFieldLabels(), $fieldMeta);
        }

        return array_intersect_key($defaults, $fieldMeta);
    }

    private function listingFieldLabels(): array
    {
        return [
            'listing_building' => 'Building',
            'listing_unit_code' => 'Unit Code',
            'listing_room_number' => 'Room Number',
            'listing_floor' => 'Floor',
            'listing_bedrooms' => 'Bedrooms',
            'listing_area' => 'Area (sqm)',
            'listing_price_per_room' => 'Price per Room',
            'listing_price_per_sqm' => 'Price per SQM',
            'listing_unit_type' => 'Unit Type',
            'listing_location_name' => 'Location Name',
            'listing_location_province' => 'Province',
            'listing_location_district' => 'District',
            'listing_reservation_deposit' => 'Reservation Deposit',
            'listing_contract_payment' => 'Contract Payment',
            'listing_installment_15_terms' => 'Installment 15 Terms',
            'listing_installment_12_terms' => 'Installment 12 Terms',
            'listing_special_installment_3_terms' => 'Special Installment 3 Terms',
            'listing_transfer_amount' => 'Transfer Amount',
            'listing_transfer_fee' => 'Transfer Fee',
            'listing_annual_common_fee' => 'Annual Common Fee',
            'listing_sinking_fund' => 'Sinking Fund',
            'listing_utility_fee' => 'Utility Fee',
            'listing_total_misc_fee' => 'Total Misc Fees',
            'listing_status' => 'Status',
            'listing_floor_plan_image' => 'Floor Plan Image',
            'listing_room_layout_image' => 'Room Layout Image',
            'user_name' => 'Sales Name',
            'user_phone' => 'Sales Phone',
        ];
    }

    private function reservationFieldLabels(): array
    {
        return [
            'reservation_buyer_first_name' => 'Buyer First Name',
            'reservation_buyer_last_name' => 'Buyer Last Name',
            'reservation_buyer_full_name' => 'Buyer Full Name',
            'reservation_buyer_id_number' => 'Buyer ID Number',
            'reservation_buyer_address' => 'Buyer Address',
            'reservation_buyer_phone' => 'Buyer Phone',
            'reservation_buyer_email' => 'Buyer Email',
            'reservation_date' => 'Reservation Date',
            'reservation_amount' => 'Reservation Amount',
            'reservation_amount_paid_number' => 'Amount Paid (Number)',
            'reservation_amount_paid_text' => 'Amount Paid (Text)',
            'reservation_contract_start_date' => 'Contract Start Date',
            'reservation_buyer_signature_name' => 'Buyer Signature Name',
            'reservation_buyer_signature_path' => 'Buyer Signature Image',
            'reservation_seller_name' => 'Seller Name',
            'reservation_seller_signature_path' => 'Seller Signature Image',
            'reservation_witness_one_name' => 'Witness #1 Name',
            'reservation_witness_one_signature_path' => 'Witness #1 Signature Image',
            'reservation_witness_two_name' => 'Witness #2 Name',
            'reservation_witness_two_signature_path' => 'Witness #2 Signature Image',
        ];
    }

    private function installmentFieldMeta(): array
    {
        $fields = [
            'buyer_full_name' => 'text',
            'buyer_phone' => 'text',
            'contract_number' => 'text',
            'contract_date' => 'date',
            'project_name' => 'text',
            'floor' => 'text',
            'room_number' => 'text',
            'unit_type' => 'text',
            'area_sqm' => 'number',
            'total_price_number' => 'number',
            'total_price_text' => 'text',
            'installment_total_number' => 'number',
            'total_term' => 'number',
            'user_name' => 'text',
            'user_phone' => 'text',
        ];

        for ($seq = 1; $seq <= 15; $seq++) {
            $fields["installment_{$seq}_amount_number"] = 'number';
            $fields["installment_{$seq}_due_date"] = 'date';
        }

        return $fields;
    }

    private function installmentFieldLabels(): array
    {
        $labels = [
            'buyer_full_name' => 'Buyer Full Name',
            'buyer_phone' => 'Buyer Phone',
            'contract_number' => 'Contract Number',
            'contract_date' => 'Contract Date',
            'project_name' => 'Project Name',
            'floor' => 'Floor',
            'room_number' => 'Room Number',
            'unit_type' => 'Unit Type',
            'area_sqm' => 'Area (sqm)',
            'total_price_number' => 'Total Price (Number)',
            'total_price_text' => 'Total Price (Text)',
            'installment_total_number' => 'Installment Total (Number)',
            'total_term' => 'Total Term',
            'user_name' => 'Sales Name',
            'user_phone' => 'Sales Phone',
        ];

        for ($seq = 1; $seq <= 15; $seq++) {
            $labels["installment_{$seq}_amount_number"] = "Installment #{$seq} Amount";
            $labels["installment_{$seq}_due_date"] = "Installment #{$seq} Due Date";
        }

        return $labels;
    }

    private function installmentSampleData(): array
    {
        $data = [
            'buyer_full_name' => 'Somchai Sukjai',
            'buyer_phone' => '089-555-0000',
            'contract_number' => 'CTR-20260101-0001',
            'contract_date' => now()->format('Y-m-d'),
            'project_name' => 'EVANTE Samui Condominium',
            'floor' => '5',
            'room_number' => '501',
            'unit_type' => 'A',
            'area_sqm' => '35.50',
            'total_price_number' => '3,850,000.00',
            'total_price_text' => 'Three Million Eight Hundred Fifty Thousand Baht',
            'installment_total_number' => '1,500,000.00',
            'total_term' => '10',
            'user_name' => auth()->user()?->name ?? 'Sales Name',
            'user_phone' => auth()->user()?->phone ?? '081-234-5678',
        ];

        for ($seq = 1; $seq <= 15; $seq++) {
            $data["installment_{$seq}_amount_number"] = '150,000.00';
            $data["installment_{$seq}_due_date"] = now()->addMonths($seq)->format('Y-m-d');
        }

        return $data;
    }

    public function store(Request $request, PdfTemplate $template)
    {
        $validated = $request->validate([
            'db_field' => 'required|string',
            'field_type' => 'required|in:text,number,date,image,checkbox',
            'x_position' => 'required|numeric|min:0',
            'y_position' => 'required|numeric|min:0',
            'page_number' => 'required|integer|min:1',
            'img_width' => 'nullable|numeric|min:1|max:300',
        ]);

        if (!isset($validated['img_width'])) {
            $validated['img_width'] = 50;
        }

        $mapping = $template->mappings()->create($validated);

        return response()->json([
            'success' => true,
            'mapping' => $mapping,
        ]);
    }

    public function update(Request $request, PdfTemplate $template, TemplateMapping $mapping)
    {
        $validated = $request->validate([
            'x_position' => 'required|numeric|min:0',
            'y_position' => 'required|numeric|min:0',
            'page_number' => 'required|integer|min:1',
            'img_width' => 'nullable|numeric|min:1|max:300',
        ]);

        $mapping->update($validated);

        return response()->json([
            'success' => true,
            'mapping' => $mapping,
        ]);
    }

    public function destroy(PdfTemplate $template, TemplateMapping $mapping)
    {
        $mapping->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function destroyTemplate(PdfTemplate $template)
    {
        if ($template->file_path) {
            Storage::disk('public')->delete($template->file_path);
        }

        $template->delete();

        return redirect()->route('templates.index')
            ->with('success', 'Template deleted successfully.');
    }
}
