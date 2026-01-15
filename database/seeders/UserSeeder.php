<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    // public function run(): void
    // {
    //     // نفترض أن عندك مدن ومناطق مسبقاً
    //     $cityIds = \DB::table('cities')->pluck('id')->toArray();
    //     $areaIds = \DB::table('areas')->get();

    //     for ($i = 1; $i <= 10; $i++) {

    //         $cityId = fake()->randomElement($cityIds);

    //         // نجيب منطقة تابعة لنفس المدينة
    //         $areaId = $areaIds
    //             ->where('city_id', $cityId)
    //             ->random()
    //             ->id;

    //         $user = User::create([
    //             'name' => "User $i Test",
    //             'phone_number' => '099' . fake()->unique()->numberBetween(1000000, 9999999),
    //             'password' => Hash::make('123456789'),
    //             'otp_code' => rand(1000, 9999),
    //             'otp_expires_at' => Carbon::now()->addMinutes(10),
    //             'is_verified' => false,
    //         ]);

    //         Profile::create([
    //             'user_id' => $user->id,
    //             'first_name' => "User$i",
    //             'last_name' => "Seeder",
    //             'birth_date' => Carbon::now()->subYears(rand(18, 40))->format('Y-m-d'),
    //             'city_id' => $cityId,
    //             'area_id' => $areaId,
    //             'profile_photo' => 'profiles/default-profile.png',
    //             'personal_photo' => 'personal/default-profile.png',
    //             'id_photo' => 'identities/default-profile.png',
    //         ]);
    //     }
    // }

    public function run(): void
    {
        $names = ['خليل 1', 'أحمد 2', 'سارة 3', 'ليلى 4', 'محمد 5', 'نور 6', 'علي 7', 'مريم 8', 'يوسف 9', 'حسين 10'];
        $phone_start = 12345671; // آخر 7 أرقام بعد 09
        $otps = ['1111','2222','3333','4444','5555','6666','7777','8888','9999','0000'];

        for ($i = 0; $i < 10; $i++) {
            DB::table('users')->insert([
                'name' => $names[$i],
                'phone_number' => '09' . ($phone_start + $i), // 0912345671 ...
                'password' => Hash::make('123456789'), // كلمة السر موحدة
                'otp_code' => $otps[$i],
                'otp_expires_at' => Carbon::now()->addMinutes(10), // وقت انتهاء OTP
                'is_verified' => false,
                'balance' => 1000000.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
