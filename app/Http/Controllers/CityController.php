<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    //
    public function cities()
    {
        return response()->json(City::all());
    }

    // دالة لجلب المناطق التابعة لمحافظة معينة
    public function areas(Request $request)
    {
        $request->validate([
            'city_id' => 'required'
        ]);

        $city = City::find($request->city_id);
        return response()->json($city->areas);
    }
}
