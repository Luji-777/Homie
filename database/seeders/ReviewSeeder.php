<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Booking;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $bookings = Booking::all();

        if ($bookings->isEmpty()) {
            $this->command->warn('No bookings found. ReviewSeeder skipped.');
            return;
        }

        foreach ($bookings as $booking) {

            // نتجنب تكرار Review لنفس الحجز
            if ($booking->review) {
                continue;
            }

            Review::create([
                'apartment_id' => $booking->apartment_id,
                'tenant_id' => $booking->tenant_id,
                'booking_id' => $booking->id,
                'rating' => rand(3, 5), // غالبًا تقييمات إيجابية
                'comment' => fake()->sentence(12),
            ]);
        }
    }
}
