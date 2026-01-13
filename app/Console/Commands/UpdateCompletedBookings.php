<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Carbon\Carbon;

class UpdateCompletedBookings extends Command
{
    protected $signature = 'bookings:update-completed';
    protected $description = 'Update bookings status to completed after check-out date';

    public function handle()
    {
        $today = Carbon::today();

        $bookings = Booking::whereIn('status', ['owner_approved', 'paid'])
            ->where('check_out', '<', $today)
            ->get();

        foreach ($bookings as $booking) {
            $booking->update(['status' => 'completed']);
        }

        $this->info($bookings->count() . ' bookings updated to completed.');
    }
}
