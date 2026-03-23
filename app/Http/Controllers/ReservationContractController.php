<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReservationContractController extends Controller
{
    public function create()
    {
        $listings = Listing::orderBy('unit_code')->with('project')->get();
        $reservations = Reservation::with('listing.project')->latest()->get();

        return view('contracts.reservation', compact('listings', 'reservations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'buyer_first_name' => 'required|string|max:255',
            'buyer_last_name' => 'required|string|max:255',
            'buyer_id_number' => 'required|string|max:255',
            'buyer_address' => 'nullable|string',
            'buyer_phone' => 'required|string|max:255',
            'buyer_email' => 'nullable|email|max:255',
            'reservation_date' => 'required|date',
            'amount_paid_number' => 'required|numeric|min:0',
            'amount_paid_text' => 'required|string|max:255',
            'contract_start_date' => 'required|date',
            'buyer_signature_name' => 'required|string|max:255',
            'seller_name' => 'required|string|max:255',
            'witness_one_name' => 'required|string|max:255',
            'witness_two_name' => 'required|string|max:255',
            'buyer_signature_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'seller_signature_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'witness_one_signature_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'witness_two_signature_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $listing = Listing::findOrFail($validated['listing_id']);
        $validated['reservation_amount'] = $listing->reservation_deposit;

        $fileMap = [
            'buyer_signature_file' => 'buyer_signature_path',
            'seller_signature_file' => 'seller_signature_path',
            'witness_one_signature_file' => 'witness_one_signature_path',
            'witness_two_signature_file' => 'witness_two_signature_path',
        ];

        foreach ($fileMap as $input => $column) {
            if ($request->hasFile($input)) {
                $validated[$column] = $request->file($input)->store('reservation-signatures', 'public');
            }
            unset($validated[$input]);
        }

        Reservation::create($validated);

        return redirect()->route('contracts.reservation.create')
            ->with('success', 'Reservation saved successfully.');
    }
}
