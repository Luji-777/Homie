<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\Apartment_image;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ApartmentSeeder extends Seeder
{
    // public function run(): void
    // {
    //     $users = User::pluck('id')->toArray();
    //     $areas = \DB::table('areas')->get();

    //     // نتأكد أن في users و areas
    //     if (empty($users) || $areas->isEmpty()) {
    //         $this->command->warn('No users or areas found, skipping ApartmentSeeder.');
    //         return;
    //     }

    //     // $types = ['room', 'studio', 'house', 'villa'];
    //     $rentTypes = ['day', 'month'];
    //     for ($i = 1; $i <= 20; $i++) {

    //         $area = $areas->random();

    //         $apartment = Apartment::create([
    //             'title' => 'Apartment ' . $i,
    //             'discription' => 'Seeder apartment description ' . $i, // نفس اسم العمود
    //             'type' => fake()->randomElement(['room', 'studio', 'house', 'villa']),
    //             'address' => 'Test Address ' . $i,
    //             'rent_type'    => $rentTypes[array_rand($rentTypes)],
    //             'price'        => rand(100, 1000),
    //             'space' => rand(50, 250),
    //             'floor' => fake()->randomElement(['ground', '1', '2', '3']),
    //             'rooms' => rand(1, 5),
    //             'bedrooms' => rand(1, 4),
    //             'bathrooms' => rand(1, 3),
    //             'wifi' => rand(0, 1),
    //             'solar' => rand(0, 1),
    //             'owner_id' => collect($users)->random(),
    //             'area_id' => $area->id,
    //             'is_approved' => false,
    //         ]);


    //         // إنشاء صور للشقة
    //         $imagesCount = rand(3, 6);

    //         for ($j = 1; $j <= $imagesCount; $j++) {

    //             Apartment_image::create([
    //                 'apartment_id' => $apartment->id,
    //                 'image_path' => 'default-profile' . $j . '.png',
    //                 'is_cover' => $j === 1, // أول صورة غلاف
    //             ]);
    //         }
    //     }
    // }
    public function run(): void
    {
        $users = DB::table('users')->pluck('id'); // جلب كل المستخدمين
        $areas = \DB::table('areas')->get();
        $apartmentIndex = 1;
        $totalImages = 20;

        foreach ($users as $userId) {

            // كل مستخدم يملك شقتين
            for ($i = 0; $i < 2; $i++) {

                if ($apartmentIndex > 20) {
                    return;
                }
                $area = $areas->random();
                // إنشاء الشقة
                $apartment = Apartment::create([
                    'owner_id'     => $userId,
                    'type'         => ['room', 'studio', 'house', 'villa'][array_rand(['room', 'studio', 'house', 'villa'])],
                    'discription'  => 'وصف تجريبي للشقة رقم ' . $apartmentIndex,
                    'title'        => 'شقة رقم ' . $apartmentIndex,
                    'address'      => 'دمشق - منطقة ' . $apartmentIndex,
                    'rent_type'    => $apartmentIndex % 2 == 0 ? 'month' : 'day',
                    'price'        => rand(100, 500),
                    'space'        => rand(60, 200),
                    'floor'        => rand(1, 5),
                    'rooms'        => rand(1, 4),
                    'bedrooms'     => rand(1, 3),
                    'bathrooms'    => rand(1, 2),
                    'wifi'         => true,
                    'solar'        => false,
                    'is_approved'  => false,
                    'area_id' => $area->id,
                    'status'       => 'pending',
                    'is_available' => true,
                ]);

                /**
                 * 🖼️ صورة الغلاف (ثابتة)
                 */
                DB::table('apartment_images')->insert([
                    'apartment_id' => $apartment->id,
                    'image_path'   => 'apartments/ap' . $apartmentIndex . '.jpg',
                    'is_cover'     => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                /**
                 * 🖼️ صور إضافية (غير غلاف)
                 */
                for ($img = 1; $img <= 2; $img++) {

                    $imageNumber = ($apartmentIndex + $img) % $totalImages;
                    if ($imageNumber == 0) {
                        $imageNumber = $totalImages;
                    }

                    DB::table('apartment_images')->insert([
                        'apartment_id' => $apartment->id,
                        'image_path'   => 'apartments/ap' . $imageNumber . '.jpg',
                        'is_cover'     => false,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }

                $apartmentIndex++;
            }
        }
    }
} 

// class ApartmentSeeder extends Seeder
// {
//     public function run(): void
//     {
//         $types = ['room', 'studio', 'house', 'villa'];
//         $rentTypes = ['day', 'month'];

//         for ($i = 1; $i <= 10; $i++) {
//             DB::table('apartments')->insert([
//                 'owner_id'     => rand(1, 5), // تأكد إنو عندك users بهالـ IDs
//                 'type'         => $types[array_rand($types)],
//                 'discription'  => 'شقة مريحة ومجهزة بالكامل للإيجار.',
//                 'title'        => 'شقة رقم ' . $i,
//                 'address'      => 'دمشق - منطقة ' . rand(1, 10),
//                 'rent_type'    => $rentTypes[array_rand($rentTypes)],
//                 'price'        => rand(100, 1000),
//                 'space'        => rand(40, 200),
//                 'floor'        => (string) rand(1, 10),
//                 'rooms'        => rand(1, 5),
//                 'bedrooms'     => rand(1, 4),
//                 'bathrooms'    => rand(1, 3),
//                 'wifi'         => rand(0, 1),
//                 'solar'        => rand(0, 1),
//                 'is_approved'  => rand(0, 1),
//                 'status'       => 'active',
//                 'is_available' => rand(0, 1),
//                 'created_at'   => now(),
//                 'updated_at'   => now(),
//             ]);
//         }
//     }
// }
