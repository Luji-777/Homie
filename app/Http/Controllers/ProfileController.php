<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class ProfileController extends Controller
{
    public function show()
    {

        $user = FacadesAuth::user();
        $user_id = $user->id;
        $profile = Profile::findOrFail($user_id);


        return response()->json([
            'profile' => [
                'id'             => $user->id,
                'first_name'     => $profile->first_name,
                'last_name'      => $profile->last_name,
                'phone_number'   => $user->phone_number,
                // 'password'       => $user->password,
                'birth_date'     => $profile->birth_date,
                'personal_photo' => $profile->personal_photo ?: null,
                'profile_photo'  => $profile->profile_photo ?: null,
                'id_photo'       => $profile->id_photo,
                'city_id'        => $profile->city_id,
                'area_id'        => $profile->area_id,
            ]

        ]);
    }



    // public function update(Request $request)
    // {
    //     $user = FacadesAuth::user();
    //     $user_id = $user->id;
    //     $profile = Profile::findOrFail($user_id);

    //     $validated = $request->validate([
    //         'first_name' => 'sometimes|string|max:255',
    //         'last_name' => 'sometimes|string|max:255',
    //         // 'phone_number' => 'digits_between:9,15|unique:users,phone_number',
    //         'password' => 'sometimes|string|min:6|max:255|confirmed',
    //         'birth_date' => 'sometimes|date|before:today',
    //         // إضافة قواعد التحقق للمحافظة والمنطقة
    //         'city_id' => 'sometimes|integer|exists:cities,id',
    //         'area_id' => [
    //             'sometimes|integer',
    //             // التأكد من أن المنطقة المختارة تابعة للمحافظة المختارة
    //             Rule::exists('areas', 'id')->where(function ($query) use ($request) {
    //                 return $query->where('city_id', $request->city_id);
    //             })
    //         ],
    //         'profile_photo' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
    //         'personal_photo' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
    //         'id_photo' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048'
    //     ]);

    //     $profile->update($validated);
    //     $user->update($validated);
    //     $profile->refresh();
    //     $user->refresh();

    //     return response()->json(
    //         [
    //             'message'   => 'Profile updated successfully.',
    //             'profile'   => $profile,
    //             'password'  => $user->password,
    //             'phone_number'  => $user->phone_number,
    //         ]
    //     );
    // }



    public function update(Request $request)
    {
        $user = FacadesAuth::user();

        // التأكد من وجود بروفايل
        $profile = $user->profile;
        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found'
            ], 404);
        }

        $request->validate([
            'first_name'     => 'sometimes|required|string|max:255',
            'last_name'      => 'sometimes|required|string|max:255',
            'current_password' => 'sometimes|required_with:password|current_password',
            'password'       => 'sometimes|required|string|min:6|max:255|confirmed',
            'birth_date'     => 'sometimes|required|date|before:today',
            'city_id'        => 'sometimes|required|integer|exists:cities,id',
            'area_id'        => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('areas', 'id')->where(function ($query) use ($request) {
                    return $query->where('city_id', $request->city_id);
                })
            ],
            'profile_photo'  => 'sometimes|nullable|image|mimes:jpeg,png,jpg|max:2048',
            'personal_photo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg|max:2048',
            'id_photo'       => 'sometimes|nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // =============================================
        // تحديث بيانات المستخدم الأساسية (User)
        // =============================================
        $userData = [];


        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        if (!empty($userData)) {
            $user->update($userData);
        }

        // =============================================
        // تحديث بيانات البروفايل
        // =============================================
        $profileData = [];

        if ($request->filled('first_name')) {
            $profileData['first_name'] = $request->first_name;
        }
        if ($request->filled('last_name')) {
            $profileData['last_name'] = $request->last_name;
        }
        if ($request->filled('birth_date')) {
            $profileData['birth_date'] = Carbon::createFromFormat('d-m-Y', $request->birth_date)
                ->format('Y-m-d');
        }
        if ($request->filled('city_id')) {
            $profileData['city_id'] = $request->city_id;
        }
        if ($request->filled('area_id')) {
            $profileData['area_id'] = $request->area_id;
        }

        // معالجة الصور
        if ($request->hasFile('profile_photo')) {
            $profileData['profile_photo'] = $request->file('profile_photo')
                ->store('profiles', 'public');
        }

        if ($request->hasFile('personal_photo')) {
            $profileData['personal_photo'] = $request->file('personal_photo')
                ->store('personal', 'public');
        }

        if ($request->hasFile('id_photo')) {
            $profileData['id_photo'] = $request->file('id_photo')
                ->store('identities', 'public');
        }

        // تحديث الاسم الكامل في جدول users إذا تغير الاسم الأول أو الأخير
        if (isset($profileData['first_name']) || isset($profileData['last_name'])) {
            $newName = ($profileData['first_name'] ?? $profile->first_name) . ' ' .
                ($profileData['last_name'] ?? $profile->last_name);

            $user->update(['name' => trim($newName)]);
        }

        // تنفيذ التحديث الفعلي للبروفايل
        if (!empty($profileData)) {
            $profile->update($profileData);
        }

        // إعادة تحميل البيانات الجديدة
        $user->refresh();
        $profile->refresh();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id'           => $user->id,
                'phone_number' => $user->phone_number,
                'name'         => $user->name,
            ],
            'profile' => [
                'first_name'     => $profile->first_name,
                'last_name'      => $profile->last_name,
                'birth_date'     => $profile->birth_date,
                'city_id'        => $profile->city_id,
                'area_id'        => $profile->area_id,
                'profile_photo'  => $profile->profile_photo ? asset('storage/' . $profile->profile_photo) : null,
                'personal_photo' => $profile->personal_photo ? asset('storage/' . $profile->personal_photo) : null,
                'id_photo'       => $profile->id_photo ? asset('storage/' . $profile->id_photo) : null,
            ]
        ]);
    }
}
