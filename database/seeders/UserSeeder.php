<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // نفترض أن عندك مدن ومناطق مسبقاً
        $cityIds = \DB::table('cities')->pluck('id')->toArray();
        $areaIds = \DB::table('areas')->get();

        for ($i = 1; $i <= 10; $i++) {

            $cityId = fake()->randomElement($cityIds);

            // نجيب منطقة تابعة لنفس المدينة
            $areaId = $areaIds
                ->where('city_id', $cityId)
                ->random()
                ->id;

            $user = User::create([
                'name' => "User $i Test",
                'phone_number' => '099' . fake()->unique()->numberBetween(1000000, 9999999),
                'password' => Hash::make('123456789'),
                'otp_code' => rand(1000, 9999),
                'otp_expires_at' => Carbon::now()->addMinutes(10),
                'is_verified' => false,
            ]);

            Profile::create([
                'user_id' => $user->id,
                'first_name' => "User$i",
                'last_name' => "Seeder",
                'birth_date' => Carbon::now()->subYears(rand(18, 40))->format('Y-m-d'),
                'city_id' => $cityId,
                'area_id' => $areaId,
                'profile_photo' => 'profiles/default-profile.png',
                'personal_photo' => 'personal/default-profile.png',
                'id_photo' => 'identities/default-profile.png',
            ]);
        }
    }
}
