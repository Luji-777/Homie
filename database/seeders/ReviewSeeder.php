<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // جلب الحجوزات المكتملة فقط
        $completedBookings = DB::table('bookings')
            ->where('status', 'completed')
            ->get();

        $comments = [
            'تجربة ممتازة، الشقة نظيفة ومريحة',
            'المكان رائع وصاحب الشقة متعاون',
            'كل شي كان تمام وأنصح بهاي الشقة',
            'موقع ممتاز وخدمة رائعة',
            'الشقة أفضل من المتوقع',
            'إقامة مريحة وسلسة',
            'نظافة عالية وتعامل محترم',
        ];

        foreach ($completedBookings as $booking) {

            DB::table('reviews')->insert([
                'apartment_id' => $booking->apartment_id,
                'tenant_id'    => $booking->tenant_id,
                'booking_id'   => $booking->id,

                'rating'  => rand(3, 5),
                'comment' => $comments[array_rand($comments)],

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
