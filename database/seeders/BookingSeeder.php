<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Apartment;
use App\Models\User;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $apartments = Apartment::with('owner')->get();
        $users = User::pluck('id')->toArray();

        if ($apartments->isEmpty() || empty($users)) {
            $this->command->warn('No apartments or users found. BookingSeeder skipped.');
            return;
        }

        foreach ($apartments as $apartment) {

            // نعمل 1 إلى 3 حجوزات لكل شقة
            $bookingsCount = rand(1, 3);

            for ($i = 0; $i < $bookingsCount; $i++) {

                // tenant مختلف عن owner
                $tenantId = collect($users)
                    ->reject(fn ($id) => $id == $apartment->owner_id)
                    ->random();

                $checkIn = Carbon::now()->addDays(rand(-30, 30));
                $checkOut = (clone $checkIn)->addDays(rand(1, 10));

                $days = $checkIn->diffInDays($checkOut);
                $pricePerDay = rand(20, 60);

                $status = collect([
                    'pending',
                    'owner_approved',
                    'owner_rejected',
                    'paid',
                    'completed',
                ])->random();

                Booking::create([
                    'apartment_id' => $apartment->id,
                    'owner_id' => $apartment->owner_id,
                    'tenant_id' => $tenantId,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'total_price' => $days * $pricePerDay,
                    'status' => $status,
                    'owner_approval' => in_array($status, ['owner_approved', 'paid', 'completed']),
                    'cancellation_reason' => $status === 'owner_rejected'
                        ? fake()->sentence()
                        : null,
                ]);
            }
        }
    }
}
