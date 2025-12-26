<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Area;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        // المحافظات
        $damascus   = City::create(['name' => 'دمشق']);
        $aleppo     = City::create(['name' => 'حلب']);
        $homs       = City::create(['name' => 'حمص']);
        $lattakia   = City::create(['name' => 'اللاذقية']);
        $tartous    = City::create(['name' => 'طرطوس']);
        $idleb      = City::create(['name' => 'إدلب']);
        $hama       = City::create(['name' => 'حماة']);
        $hasaka     = City::create(['name' => 'الحسكة']);
        $daraa      = City::create(['name' => 'درعا']);
        $sweda      = City::create(['name' => 'السويداء']);
        $deralzor   = City::create(['name' => 'دير الزور']);
        $raka       = City::create(['name' => 'الرقة']);
        $qunaitera  = City::create(['name' => 'القنيطرة']);

        // ================= دمشق =================
        $damascusAreas = [
            'المزة', 'المالكي', 'أبو رمانة', 'المهاجرين', 'ركن الدين',
            'القصاع', 'باب توما', 'باب شرقي', 'البرامكة',
            'كفرسوسة', 'دمر', 'مشروع دمر', 'جرمانا',
            'الزاهرة', 'الميدان', 'الشعلان'
        ];

        foreach ($damascusAreas as $area) {
            Area::create(['name' => $area, 'city_id' => $damascus->id]);
        }

        // ================= حلب =================
        $aleppoAreas = [
            'السليمانية', 'العزيزية', 'الجميلية', 'الشارقة',
            'صلاح الدين', 'السكري', 'الحمدانية',
            'الأنصاري', 'الزهراء', 'الأشرفية'
        ];

        foreach ($aleppoAreas as $area) {
            Area::create(['name' => $area, 'city_id' => $aleppo->id]);
        }

        // ================= حمص =================
        $homsAreas = [
            'الحميدية', 'الوعر', 'كرم الشامي', 'باب السباع',
            'باب الدريب', 'الخالدية', 'القصور'
        ];

        foreach ($homsAreas as $area) {
            Area::create(['name' => $area, 'city_id' => $homs->id]);
        }

        // ================= اللاذقية =================
        $lattakiaAreas = [
            'الصليبة', 'الرمل الجنوبي', 'الرمل الشمالي',
            'مشروع الزراعة', 'دمسرخو', 'الزقزقانية'
        ];

        foreach ($lattakiaAreas as $area) {
            Area::create(['name' => $area, 'city_id' => $lattakia->id]);
        }

        // ================= طرطوس =================
        $tartousAreas = [
            'الكرامة', 'الشيخ سعد', 'القدموس',
            'صافيتا', 'بانياس'
        ];

        foreach ($tartousAreas as $area) {
            Area::create(['name' => $area, 'city_id' => $tartous->id]);
        }

        // ================= إدلب =================
        $idlebAreas = [
            'المدينة', 'سراقب', 'معرة النعمان',
            'أريحا', 'الدانا'
        ];

        foreach ($idlebAreas as $area) {
            Area::create(['name' => $area, 'city_id' => $idleb->id]);
        }

        // ================= حماة =================
        $hamaAreas = [
            'المرابط', 'القصور', 'الحاضر',
            'السوق', 'طريق حلب'
        ];

        foreach ($hamaAreas as $area) {
            Area::create(['name' => $area, 'city_id' => $hama->id]);
        }

        // ================= الحسكة =================
        $hasakaAreas = [
            'القامشلي', 'رأس العين', 'الشدادي',
            'الدرباسية', 'المالكية'
        ];

        foreach ($hasakaAreas as $area) {
            Area::create(['name' => $area, 'city_id' => $hasaka->id]);
        }

        // ================= درعا =================
        $daraaAreas = [
            'درعا البلد', 'درعا المحطة',
            'نوى', 'طفس', 'الصنمين'
        ];

        foreach ($daraaAreas as $area) {
            Area::create(['name' => $area, 'city_id' => $daraa->id]);
        }

        // ================= السويداء =================
        $swedaAreas = [
            'شهبا', 'صلخد', 'القريا',
            'المزرعة', 'عرمان'
        ];

        foreach ($swedaAreas as $area) {
            Area::create(['name' => $area, 'city_id' => $sweda->id]);
        }

        // ================= دير الزور =================
        $deralzorAreas = [
            'الحميدية', 'الجبيلة', 'الحويقة',
            'الموظفين', 'الرصافة'
        ];

        foreach ($deralzorAreas as $area) {
            Area::create(['name' => $area, 'city_id' => $deralzor->id]);
        }

        // ================= الرقة =================
        $rakaAreas = [
            'المنصور', 'الرميلة',
            'الدرعية', 'المشلب'
        ];

        foreach ($rakaAreas as $area) {
            Area::create(['name' => $area, 'city_id' => $raka->id]);
        }

        // ================= القنيطرة =================
        $qunaiteraAreas = [
            'خان أرنبة', 'جباتا الخشب',
            'مسعدة', 'حضر'
        ];

        foreach ($qunaiteraAreas as $area) {
            Area::create(['name' => $area, 'city_id' => $qunaitera->id]);
        }
    }
}
