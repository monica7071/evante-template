<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Models\Sale;
use Illuminate\Console\Command;

class ExpireAppointments extends Command
{
    protected $signature   = 'appointments:expire';
    protected $description = 'Auto-cancel appointments whose date has passed and revert to available';

    public function handle(): int
    {
        $expired = Sale::where('status', 'appointment')
            ->whereHas('appointment', function ($q) {
                $q->whereDate('appointment_date', '<', now()->toDateString());
            })
            ->with('appointment')
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired appointments found.');
            return self::SUCCESS;
        }

        foreach ($expired as $sale) {
            $sale->appointment?->delete();

            $sale->update([
                'status'           => 'available',
                'previous_status'  => 'appointment',
            ]);

            $sale->statusHistories()->create([
                'status'          => 'available',
                'previous_status' => 'appointment',
                'notes'           => 'Appointment date passed — auto-cancelled by system',
                'user_id'         => null,
            ]);

            Listing::where('id', $sale->listing_id)->update(['status' => 'available']);
        }

        $this->info("Expired {$expired->count()} appointment(s) and reverted to available.");

        return self::SUCCESS;
    }
}
