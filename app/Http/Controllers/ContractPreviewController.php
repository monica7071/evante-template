<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\PdfTemplate;

class ContractPreviewController extends Controller
{
    public function show(Contract $contract)
    {
        $template = PdfTemplate::where('contract_type', $contract->type)
            ->with('mappings')
            ->latest()
            ->first();

        if (!$template) {
            return redirect()->route('dashboard')
                ->with('error', 'No PDF template found for this contract type. Please upload a template first.');
        }

        $contractData = $this->contractData($contract);

        return view('contracts.preview', compact('contract', 'template', 'contractData'));
    }

    private function contractData(Contract $contract): array
    {
        return [
            'buyer_name' => $contract->buyer_name,
            'id_number' => $contract->id_number,
            'phone' => $contract->phone,
            'email' => $contract->email,
            'unit_number' => $contract->unit_number,
            'price' => number_format((float) $contract->price, 2),
            'deposit' => $contract->deposit !== null ? number_format((float) $contract->deposit, 2) : '',
            'contract_date' => optional($contract->contract_date)->format('Y-m-d'),
            'image_path' => $contract->image_path ? asset('storage/' . $contract->image_path) : null,
        ];
    }
}
