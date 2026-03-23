<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;

class PurchaseContractController extends Controller
{
    public function create()
    {
        $contracts = Contract::where('type', 'purchase')
            ->latest()
            ->get();

        return view('contracts.purchase', compact('contracts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'buyer_name' => 'required|string|max:255',
            'id_number' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'unit_number' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'contract_date' => 'required|date',
        ]);

        $validated['type'] = 'purchase';

        $contract = Contract::create($validated);

        return redirect()->route('contracts.preview', $contract)
            ->with('success', 'Purchase contract created successfully.');
    }
}
