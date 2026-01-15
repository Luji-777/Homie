<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Apartment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;



class BookingSeeder extends Seeder
{
    // public function run(): void
    // {
    //     $statuses = [
    //         'pending',
    //         'owner_approved',
    //         'owner_rejected',
    //         'cancelled',
    //         'completed',
    //     ];

    //     $requestStatuses = [
    //         'new',
    //         'pending_owner',
    //         'owner_accepted',
    //         'owner_rejected',
    //         'tenant_cancel_request',
    //         'owner_cancel_accepted',
    //         'owner_cancel_rejected',
    //         'tenant_modify_request',
    //         'owner_modify_accepted',
    //         'owner_modify_rejected',
    //         'completed',
    //     ];

    //     for ($i = 1; $i <= 10; $i++) {

    //         $checkIn  = Carbon::now()->addDays(rand(1, 20));
    //         $checkOut = (clone $checkIn)->addDays(rand(1, 7));

    //         $status = $statuses[array_rand($statuses)];

    //         DB::table('bookings')->insert([
    //             'apartment_id' => rand(1, 10), // تأكد إنو عندك شقق بهالـ IDs
    //             'tenant_id'    => rand(2, 10), // مستأجر
    //             'owner_id'     => 1,           // مالك (مثلاً user ID = 1)

    //             'check_in'     => $checkIn->toDateString(),
    //             'check_out'    => $checkOut->toDateString(),
    //             'total_price'  => rand(200, 3000),

    //             'status'       => $status,
    //             'request_status' => $requestStatuses[array_rand($requestStatuses)],

    //             'cancellation_reason' => $status === 'cancelled'
    //                 ? 'تم الإلغاء بسبب تغيير بالخطة'
    //                 : null,

    //             'cancelled_at' => $status === 'cancelled'
    //                 ? Carbon::now()
    //                 : null,

    //             'created_at'   => now(),
    //             'updated_at'   => now(),
    //         ]);
    //     }
    // }
    public function run(): void
    {
        $users = DB::table('users')->pluck('id')->toArray();

        // ✅ الحالات المطلوبة بالضبط (20)
        $statuses = [
            // 5 مكتملة
            'completed', 'completed', 'completed', 'completed', 'completed',

            // 4 ملغية
            'cancelled', 'cancelled', 'cancelled', 'cancelled',

            // 4 منتظرة
            'pending', 'pending', 'pending', 'pending',

            // 5 مقبولة
            'owner_approved', 'owner_approved', 'owner_approved', 'owner_approved', 'owner_approved',

            // 2 مرفوضة
            'owner_rejected', 'owner_rejected',
        ];

        foreach ($statuses as $index => $status) {

            // المستأجر
            $tenantId = $users[$index % count($users)];

            // المالك (التالي)
            $ownerId = $users[($index + 1) % count($users)];

            // جلب شقة للمالك
            $apartment = DB::table('apartments')
                ->where('owner_id', $ownerId)
                ->first();

            if (!$apartment) {
                continue;
            }

            // إعداد افتراضي
            $cancelledAt = null;
            $cancelReason = null;

            // تواريخ
            if ($status === 'completed') {
                $checkIn  = Carbon::now()->subDays(rand(20, 40));
                $checkOut = (clone $checkIn)->addDays(rand(2, 5));
            } else {
                $checkIn  = Carbon::now()->addDays(rand(2, 10));
                $checkOut = (clone $checkIn)->addDays(rand(2, 5));
            }

            // request_status حسب الحالة
            $requestStatus = match ($status) {
                'pending'         => 'rent_request',
                'owner_approved'  => 'rent_approved',
                'owner_rejected'  => 'rent_rejected',
                'cancelled'       => 'cancellation_approved',
                'completed'       => 'completed',
            };

            // بيانات الإلغاء
            if ($status === 'cancelled') {
                $cancelledAt = Carbon::now()->subDays(rand(1, 10));
                $cancelReason = 'تم إلغاء الحجز';
            }

            DB::table('bookings')->insert([
                'apartment_id' => $apartment->id,
                'tenant_id'    => $tenantId,
                'owner_id'     => $ownerId,

                'check_in'  => $checkIn,
                'check_out' => $checkOut,

                'total_price' => rand(150000, 600000),

                'status'         => $status,
                'request_status' => $requestStatus,

                'cancellation_reason' => $cancelReason,
                'cancelled_at'        => $cancelledAt,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
