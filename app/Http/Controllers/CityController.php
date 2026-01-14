<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    // //
    // public function cities()
    // {
    //     return response()->json(City::all());
    // }

    // // دالة لجلب المناطق التابعة لمحافظة معينة
    // public function areas(Request $request)
    // {
    //     $request->validate([
    //         'city_id' => 'required'
    //     ]);

    //     $city = City::find($request->city_id);
    //     return response()->json($city->areas);
    // }
    public function cities()
    {
        $cities = City::all()->map(function ($city) {
            return [
                'id'   => $city->id,
                'name' => __('cities.' . $city->name)
            ];
        });

        return response()->json($cities);
    }

    // دالة لجلب المناطق التابعة لمحافظة معينة
    public function areas(Request $request)
    {
        $request->validate([
            'city_id' => 'required'
        ]);

        $city = City::find($request->city_id);
        $areas = $city->areas->map(function ($area) {
            return [
                'id'   => $area->id,
                'name' => __('areas.' . $area->name) // لو عندك ملف areas.php ممكن نعمل نفس الطريقة
            ];
        });

        return response()->json($areas);
    }
}
