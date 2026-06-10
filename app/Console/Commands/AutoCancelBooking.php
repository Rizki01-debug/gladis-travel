<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Booking;
use App\Models\DriverEarning;

class AutoCancelBooking extends Command
{
    /**
     * Command Signature
     */
    protected $signature = 'booking:auto-cancel';

    /**
     * Command Description
     */
    protected $description = 'Membatalkan booking yang sudah melewati tanggal keberangkatan namun masih pending';

    /**
     * Execute Command
     */
    public function handle()
    {
        // ================= BOOKING EXPIRED =================
        $bookings = Booking::where('status', 'pending')
            ->whereDate('departure_date', '<', today())
            ->get();

        $count = 0;

        foreach ($bookings as $booking) {

            // CANCEL BOOKING
            $booking->update([
                'status' => 'cancelled'
            ]);

            // CANCEL DRIVER EARNING
            DriverEarning::where('booking_id', $booking->id)
                ->update([
                    'status' => 'cancelled'
                ]);

            $count++;

            $this->info("Booking #{$booking->id} dibatalkan.");
        }

        $this->info("Selesai. Total booking dibatalkan: {$count}");

        return self::SUCCESS;
    }
}