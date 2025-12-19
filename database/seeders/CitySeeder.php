<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Area;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
     // إنشاء المحافظات
        $damascus = City::create(['name' => 'دمشق']);
        $aleppo = City::create(['name' => 'حلب']);
        $homs = City::create(['name' => 'حمص']);
        $lattakia = City::create(['name' => 'اللاذقية']);
        $tartous = City::create(['name' => 'طرطوس']);
        $idleb = City::create(['name' => 'إدلب']);
        $hama = City::create(['name' => 'حماة']);
        $hasaka = City::create(['name' => 'الحسكة']);
        $daraa = City::create(['name' => 'درعا']);
        $sweda = City::create(['name' => 'السويداء']);
        $deralzor = City::create(['name' => 'ديرالزور']);
        $raka = City::create(['name' => 'الرقة']);
        $qunaitera = City::create(['name' => 'القنيطرة']);


        // إنشاء مناطق تابعة لدمشق
        Area::create(['name' => 'المزة', 'city_id' => $damascus->id]);
        Area::create(['name' => 'المالكي', 'city_id' => $damascus->id]);

        // إنشاء مناطق تابعة لحلب
        Area::create(['name' => 'السليمانية', 'city_id' => $aleppo->id]);
        Area::create(['name' => 'العزيزية', 'city_id' => $aleppo->id]);

        // إنشاء مناطق تابعة لحمص
        Area::create(['name' => 'السليمانية', 'city_id' => $homs->id]);
        Area::create(['name' => 'العزيزية', 'city_id' => $homs->id]);

        // إنشاء مناطق تابعة لاللاذقية
        Area::create(['name' => 'السليمانية', 'city_id' => $lattakia->id]);
        Area::create(['name' => 'العزيزية', 'city_id' => $lattakia->id]);

        // إنشاء مناطق تابعة لطرطوس
        Area::create(['name' => 'السليمانية', 'city_id' => $tartous->id]);
        Area::create(['name' => 'العزيزية', 'city_id' => $tartous->id]);

        // إنشاء مناطق تابعة لإدلب
        Area::create(['name' => 'السليمانية', 'city_id' => $idleb->id]);
        Area::create(['name' => 'العزيزية', 'city_id' => $idleb->id]);

        // إنشاء مناطق تابعة لحماة
        Area::create(['name' => 'السليمانية', 'city_id' => $hama->id]);
        Area::create(['name' => 'العزيزية', 'city_id' => $hama->id]);

        // إنشاء مناطق تابعة للحسكة
        Area::create(['name' => 'السليمانية', 'city_id' => $hasaka->id]);
        Area::create(['name' => 'العزيزية', 'city_id' => $hasaka->id]);

        // إنشاء مناطق تابعة لدرعا
        Area::create(['name' => 'السليمانية', 'city_id' => $daraa->id]);
        Area::create(['name' => 'العزيزية', 'city_id' => $daraa->id]);

        // إنشاء مناطق تابعة للسويداء
        Area::create(['name' => 'السليمانية', 'city_id' => $sweda->id]);
        Area::create(['name' => 'العزيزية', 'city_id' => $sweda->id]);

        // إنشاء مناطق تابعة لديرالزور
        Area::create(['name' => 'السليمانية', 'city_id' => $deralzor->id]);
        Area::create(['name' => 'العزيزية', 'city_id' => $deralzor->id]);
        
        // إنشاء مناطق تابعة للرقة
        Area::create(['name' => 'السليمانية', 'city_id' => $raka->id]);
        Area::create(['name' => 'العزيزية', 'city_id' => $raka->id]);

        // إنشاء مناطق تابعة للقنيطرة
        Area::create(['name' => 'السليمانية', 'city_id' => $qunaitera->id]);
        Area::create(['name' => 'العزيزية', 'city_id' => $qunaitera->id]);

        //
    }
}
