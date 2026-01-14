<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    $this->call([
        CitySeeder::class,
        UserSeeder::class,
        ApartmentSeeder::class,
        BookingSeeder::class,
        ReviewSeeder::class,
    ]);
}


        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }

