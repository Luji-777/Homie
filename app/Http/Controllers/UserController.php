<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|digits_between:9,15|unique:users,phone_number',
            'email' => 'required|string|max:255|unique:users,email',
            'password' => 'required|string|min:6|max:255|confirmed',
            'birth_date' => 'required|date|before:today'
            //'personal_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            //'id_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $otp = rand(100000, 999999); // توليد رمز تحقق عشوائي من 6 أرقام
        $birthDate = Carbon::createFromFormat('d-m-Y', $request->birth_date)->format('Y-m-d'); // شكل التاريخ
        // $personalPhotoPath = $request->file('personal_photo')->store('personal_photo', 'public'); // تخزين صورة المستخدم
        // $idPhotoPath = $request->file('id_photo')->store('id_photo', 'public'); // تخزين صورة الهوية

        $user = User::create([

            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10), // صلاحية الرمز 10 دقايق
            'is_verified' => false
        ]);

        Profile::create([
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'birth_date' => $birthDate
            // 'personal_photo' => $personalPhotoPath,
            //'id_photo' => $idPhotoPath
        ]);

        return response()->json([
            'message' => 'the account created successfully, Please verify your phone number.',
            'otp'     => $otp,                           // حذف
            'phone_number'   => $request->phone_number,
            'next_step' => 'verify-otp'
        ]);
    }




    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'otp'   => 'required|digits:6'
        ]);

        $user = User::where('phone_number', $request->phone_number)->firstOrFail();

        if ($user->otp_code !== $request->otp || Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['message' => 'the code isnot true'], 422);
        }

        // إذا كان الحساب لسا ما تمت الموافقة عليه

        if (!$user->is_verified) {
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();

            return response()->json([
                'message' => 'waiting the admin to approve the account.',
                'status'  => 'pending_approval'
            ]);
        }
    }


    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|digits_between:9,15',
            'password' => 'required|string|min:6|max:255'
        ]);
        if (!Auth::attempt($request->only('phone_number', 'password'))) 
            {
                return response()->json([
                    'message' => 'Invalid login details'
                ], 401);
            }
            $user= User::where('phone_number', $request->phone_number)->first();        
            $token= $user->createToken('auth_Token')->plainTextToken;
            return response()->json([
                'message' => 'Login successful',
                'user' => FacadesAuth::user(),
                'Token' => $token
            ],201);
            /*

        *//*
        $user = User::where('phone_number', $request->phone_number)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
        }
        return response()->json([
            'message' => 'Login successful',
            'user' => $user
        ]);
        */
    }

    public function logout(Request $request){
        // Logic for logging out the user
        $request->user()->currentAccessToken()->delete();
            return response()->json([
            'message' => 'Logout successful'
        ],204);
    }
}
