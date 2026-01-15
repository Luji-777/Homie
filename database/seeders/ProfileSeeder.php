<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->pluck('id');
        $areas = \DB::table('areas')->get();
        $cities = \DB::table('cities')->get();
        $firstNames = [
            'خليل',
            'أحمد',
            'سارة',
            'ليلى',
            'محمد',
            'نور',
            'علي',
            'مريم',
            'يوسف',
            'حسين'
        ];

        $lastNames = [
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10'
        ];

        foreach ($users as $index => $userId) {

            $i = $index + 1;
            $area = $areas->random();
            $city = $cities->where('id', $area->city_id)->first();
            DB::table('profiles')->insert([
                'user_id'        => $userId,
                'first_name'     => $firstNames[$index],
                'last_name'      => $lastNames[$index],
                'birth_date'     => Carbon::now()->subYears(rand(20, 35))->format('Y-m-d'),

                // 🖼️ الصور
                'profile_photo'  => 'profiles/user' . $i . '.jpg',
                'personal_photo' => 'personal/user' . $i . '.jpg',
                'id_photo'       => 'identities/user' . $i . '.jpg',
                'area_id'       => $area->id,
                'city_id'       => $city->id,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }
}
