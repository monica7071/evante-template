<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaleAppointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    // GET /api/v1/appointments/slots?date=2026-04-02
    public function slots(Request $request): JsonResponse
    {
        $request->validate(['date' => 'required|date']);

        $date = $request->date;
        $allSlots = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'];

        // Count bookings per slot (multiple bookings per slot allowed — round-robin to different agents)
        $bookedCounts = SaleAppointment::where('appointment_date', $date)
            ->selectRaw("substr(appointment_time, 1, 5) as slot, count(*) as cnt")
            ->groupBy('slot')
            ->pluck('cnt', 'slot')
            ->toArray();

        $available = collect($allSlots)->map(fn ($slot) => [
            'time'      => $slot,
            'available' => true,
            'booked'    => (int) ($bookedCounts[$slot] ?? 0),
        ]);

        return response()->json([
            'success' => true,
            'date'    => $date,
            'data'    => $available,
        ]);
    }

    // POST /api/v1/notify/reminder
    public function sendReminder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sale_number' => 'required|string',
            'type'        => 'required|in:appointment,payment',
            'channel'     => 'nullable|in:sms,email,line',
            'message'     => 'nullable|string|max:500',
        ]);

        // Placeholder — integrate notification service (LINE, SMS, email)
        return response()->json([
            'success' => true,
            'data'    => [
                'sale_number' => $validated['sale_number'],
                'type'        => $validated['type'],
                'channel'     => $validated['channel'] ?? 'line',
                'status'      => 'pending_integration',
                'note'        => 'Notification service not yet configured. Integrate LINE/SMS/email provider.',
            ],
        ]);
    }
}
