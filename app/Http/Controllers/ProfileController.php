<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class ProfileController extends Controller
{
    public function show(){

        $user=FacadesAuth::user();
        $user_id = $user->id;
        $profile=Profile::findOrFail($user_id);


        return response()->json([
                'profile' => [
                'id'             => $user->id,
                'first_name'     => $profile->first_name,
                'last_name'      => $profile->last_name,
                'phone_number'   => $user->phone_number,
                'password'       => $user->password,
                'birth_date'     => $profile->birth_date,
                'personal_photo' => $profile->personal_photo ? asset('storage/' . $profile->profile_image) : null,
                'profile_photo'  => $profile->profile_photo ? asset('storage/' . $profile->id_image) : null,
                'id_photo'       => $profile->id_photo,
                'city_id'        => $profile->city_id,
                'area_id'        => $profile->area_id,
            ]

                ]);
    }
    public function update(Request $request){
        $user=FacadesAuth::user();
        $user_id = $user->id;
        $profile=Profile::findOrFail($user_id);

        $validated=$request->validate([
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'phone_number' => 'digits_between:9,15|unique:users,phone_number',
            'password' => 'string|min:6|max:255|confirmed',
            'birth_date' => 'date|before:today',
            // إضافة قواعد التحقق للمحافظة والمنطقة
            'city_id' => 'integer|exists:cities,id',
            'area_id' => [
                'integer',
                // التأكد من أن المنطقة المختارة تابعة للمحافظة المختارة
                Rule::exists('areas', 'id')->where(function ($query) use ($request) {
                    return $query->where('city_id', $request->city_id);
                })
            ],
            'profile_photo' => 'image|mimes:jpeg,png,jpg|max:2048',
            'personal_photo' => 'image|mimes:jpeg,png,jpg|max:2048',
            'id_photo' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

                $profile->update($validated);
                $user->update($validated);

                return response()->json([
                'message'   => 'Profile updated successfully.',
                'profile'   => $profile,
                'password'  =>$user->password,
                'phone_number'  =>$user->phone_number,
                ]
                );


    }
}
