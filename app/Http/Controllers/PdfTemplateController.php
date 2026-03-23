<?php

namespace App\Http\Controllers;

use App\Models\PdfTemplate;
use Illuminate\Http\Request;

class PdfTemplateController extends Controller
{
    public function create()
    {
        return view('page.upload-file');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_type' => 'required|in:quotation,reservation_agreement,addendum_to_agreement,agreement_to_sell_and_purchase,contract_amendment,overdue_installment_reminder_1,overdue_installment_reminder_2,property_ownership_transfer_appointment,contract_termination_and_forfeiture,deal_slip',
            'language' => 'required|in:th,en',
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $path = $request->file('file')->store('templates', 'public');

        PdfTemplate::create([
            'contract_type' => $validated['contract_type'],
            'language' => $validated['language'],
            'file_path' => $path,
        ]);

        return redirect()->route('upload-template.create')->with('success', 'PDF template uploaded successfully.');
    }
}
