<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReservationSignatureController extends Controller
{
    private const SIGNER_CONFIG = [
        'buyer' => [
            'title'          => 'Buyer Sign / ลงชื่อผู้จอง',
            'subtitle'       => 'Buyer Sign Agreement / ผู้จองลงนามข้อตกลง',
            'name_column'    => 'buyer_signature_name',
            'sig_column'     => 'buyer_signature_path',
            'signed_column'  => 'buyer_signed_at',
            'name_fallback'  => ['buyer_full_name', 'buyer_first_name'],
        ],
        'witness1' => [
            'title'          => 'Witness 1 Sign / ลงชื่อพยาน 1',
            'subtitle'       => 'Witness 1 Sign Agreement / พยานคนที่ 1 ลงนาม',
            'name_column'    => 'witness_one_name',
            'sig_column'     => 'witness_one_signature_path',
            'signed_column'  => 'witness_one_signed_at',
            'name_fallback'  => ['witness_one_name'],
        ],
        'witness2' => [
            'title'          => 'Witness 2 Sign / ลงชื่อพยาน 2',
            'subtitle'       => 'Witness 2 Sign Agreement / พยานคนที่ 2 ลงนาม',
            'name_column'    => 'witness_two_name',
            'sig_column'     => 'witness_two_signature_path',
            'signed_column'  => 'witness_two_signed_at',
            'name_fallback'  => ['witness_two_name'],
        ],
    ];

    public function show(Sale $sale, string $type)
    {
        $config = self::SIGNER_CONFIG[$type] ?? abort(404);

        $reservation = Reservation::where('listing_id', $sale->listing_id)->latest()->firstOrFail();
        $sale->load('listing.project');

        // Pre-fill name
        $prefillName = '';
        foreach ($config['name_fallback'] as $col) {
            if (!empty($reservation->{$col})) {
                $prefillName = $reservation->{$col};
                break;
            }
        }

        // Check if already signed
        $signedAt = $reservation->{$config['signed_column']};
        $signerName = $reservation->{$config['name_column']};

        return view('contracts.signatures.form', [
            'sale'         => $sale,
            'reservation'  => $reservation,
            'type'         => $type,
            'title'        => $config['title'],
            'subtitle'     => $config['subtitle'],
            'prefillName'  => $prefillName,
            'signedAt'     => $signedAt,
            'signerName'   => $signerName,
            'projectName'  => $sale->listing->project->name ?? '-',
            'unitCode'     => $sale->listing->unit_code ?? '-',
        ]);
    }

    public function save(Request $request, Sale $sale, string $type)
    {
        $config = self::SIGNER_CONFIG[$type] ?? abort(404);

        $request->validate([
            'signer_name' => 'required|string|max:255',
            'signature'   => 'required|string',
        ]);

        $reservation = Reservation::where('listing_id', $sale->listing_id)->latest()->firstOrFail();

        // Decode base64 signature image
        $imageData = $request->input('signature');
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        $decodedImage = base64_decode($imageData);

        // Store signature file
        $fileName = "signatures/reservation/{$reservation->id}_{$type}_" . time() . '.png';
        Storage::disk('public')->put($fileName, $decodedImage);

        // Update reservation
        $reservation->update([
            $config['name_column']   => $request->input('signer_name'),
            $config['sig_column']    => $fileName,
            $config['signed_column'] => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Signature saved successfully.',
        ]);
    }
}
