<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\Apartment_image;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ApartmentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::pluck('id')->toArray();
        $areas = \DB::table('areas')->get();

        // نتأكد أن في users و areas
        if (empty($users) || $areas->isEmpty()) {
            $this->command->warn('No users or areas found, skipping ApartmentSeeder.');
            return;
        }

        for ($i = 1; $i <= 20; $i++) {

            $area = $areas->random();

            $apartment = Apartment::create([
                'title' => 'Apartment ' . $i,
                'discription' => 'Seeder apartment description ' . $i, // نفس اسم العمود
                'type' => fake()->randomElement(['room', 'studio', 'house', 'villa']),
                'address' => 'Test Address ' . $i,
                'price_per_day' => rand(10, 50),
                'price_per_month' => rand(300, 1200),
                'space' => rand(50, 250),
                'floor' => fake()->randomElement(['ground', '1', '2', '3']),
                'rooms' => rand(1, 5),
                'bedrooms' => rand(1, 4),
                'bathrooms' => rand(1, 3),
                'wifi' => rand(0, 1),
                'solar' => rand(0, 1),
                'owner_id' => collect($users)->random(),
                'area_id' => $area->id,
                'is_approved' => false,
            ]);
            

            // إنشاء صور للشقة
            $imagesCount = rand(3, 6);

            for ($j = 1; $j <= $imagesCount; $j++) {

                Apartment_image::create([
                    'apartment_id' => $apartment->id,
                    'image_path' => 'default-profile' . $j . '.png',
                    'is_cover' => $j === 1, // أول صورة غلاف
                ]);
            }
        }
    }
}
