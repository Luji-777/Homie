<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Apartment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


// class BookingSeeder extends Seeder
// {
//     public function run(): void
//     {
//         $apartments = Apartment::with('owner')->get();
//         $users = User::pluck('id')->toArray();

//         if ($apartments->isEmpty() || empty($users)) {
//             $this->command->warn('No apartments or users found. BookingSeeder skipped.');
//             return;
//         }

//         foreach ($apartments as $apartment) {

//             // نعمل 1 إلى 3 حجوزات لكل شقة
//             $bookingsCount = rand(1, 3);

//             for ($i = 0; $i < $bookingsCount; $i++) {

//                 // tenant مختلف عن owner
//                 $tenantId = collect($users)
//                     ->reject(fn ($id) => $id == $apartment->owner_id)
//                     ->random();

//                 $checkIn = Carbon::now()->addDays(rand(-30, 30));
//                 $checkOut = (clone $checkIn)->addDays(rand(1, 10));

//                 $days = $checkIn->diffInDays($checkOut);
//                 $pricePerDay = rand(20, 60);

//                 $status = collect([
//                     'pending',
//                     'owner_approved',
//                     'owner_rejected',
//                     'paid',
//                     'completed',
//                 ])->random();

//                 Booking::create([
//                     'apartment_id' => $apartment->id,
//                     'owner_id' => $apartment->owner_id,
//                     'tenant_id' => $tenantId,
//                     'check_in' => $checkIn,
//                     'check_out' => $checkOut,
//                     'total_price' => $days * $pricePerDay,
//                     'status' => $status,
//                     'owner_approval' => in_array($status, ['owner_approved', 'paid', 'completed']),
//                     'cancellation_reason' => $status === 'owner_rejected'
//                         ? fake()->sentence()
//                         : null,
//                 ]);
//             }
//         }
//     }
// }

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            'pending',
            'owner_approved',
            'owner_rejected',
            'cancelled',
            'completed',
        ];

        $requestStatuses = [
            'new',
            'pending_owner',
            'owner_accepted',
            'owner_rejected',
            'tenant_cancel_request',
            'owner_cancel_accepted',
            'owner_cancel_rejected',
            'tenant_modify_request',
            'owner_modify_accepted',
            'owner_modify_rejected',
            'completed',
        ];

        for ($i = 1; $i <= 10; $i++) {

            $checkIn  = Carbon::now()->addDays(rand(1, 20));
            $checkOut = (clone $checkIn)->addDays(rand(1, 7));

            $status = $statuses[array_rand($statuses)];

            DB::table('bookings')->insert([
                'apartment_id' => rand(1, 10), // تأكد إنو عندك شقق بهالـ IDs
                'tenant_id'    => rand(2, 10), // مستأجر
                'owner_id'     => 1,           // مالك (مثلاً user ID = 1)

                'check_in'     => $checkIn->toDateString(),
                'check_out'    => $checkOut->toDateString(),
                'total_price'  => rand(200, 3000),

                'status'       => $status,
                'request_status' => $requestStatuses[array_rand($requestStatuses)],

                'cancellation_reason' => $status === 'cancelled'
                    ? 'تم الإلغاء بسبب تغيير بالخطة'
                    : null,

                'cancelled_at' => $status === 'cancelled'
                    ? Carbon::now()
                    : null,

                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
