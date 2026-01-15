<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->pluck('id')->toArray();
        $apartments = DB::table('apartments')->pluck('id', 'owner_id')->toArray();

        foreach ($users as $tenantId) {

            // شقق ليست مملوكة من هذا المستخدم
            $availableApartments = DB::table('apartments')
                ->where('owner_id', '<>', $tenantId)
                ->pluck('id')
                ->shuffle()
                ->take(2); // شقتين فقط

            foreach ($availableApartments as $apartmentId) {
                DB::table('favorites')->insert([
                    'tenant_id'    => $tenantId,
                    'apartment_id' => $apartmentId,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }
}
