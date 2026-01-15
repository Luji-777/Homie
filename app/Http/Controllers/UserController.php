<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // تسجيل مستخدم جديد
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|digits_between:9,15|unique:users,phone_number',
            'password' => 'required|string|min:6|max:255|confirmed',
            'birth_date' => 'required|date|before:today',

            // إضافة قواعد التحقق للمحافظة والمنطقة
            'city_id' => 'required|integer|exists:cities,id',
            'area_id' => [
                'required',
                'integer',
                // التأكد من أن المنطقة المختارة تابعة للمحافظة المختارة
                Rule::exists('areas', 'id')->where(function ($query) use ($request) {
                    return $query->where('city_id', $request->city_id);
                })
            ],

            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'personal_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'id_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $otp = rand(1000, 9999); // توليد رمز تحقق عشوائي من 4 أرقام

        session(['otp' => $otp]);
        session(['otp_phone' => $request->phone_number]);

        // أرسل عبر واتساب
        sendWhatsAppMessage(
            $request->phone_number,
            "Your confirmation code is: {$otp}. Do not share it with anyone."
        );


        $birthDate = Carbon::createFromFormat('d-m-Y', $request->birth_date)->format('Y-m-d'); // شكل التاريخ

        // معالجة صور المستخدم
        $ProfilePhotoPath = 'profiles/default-profile.png'; // الصورة الافتراضية الافتراضية;
        if ($request->hasFile('profile_photo')) {
            $ProfilePhotoPath = $request->file('profile_photo')->store('profiles', 'public'); // تخزين الصورة الشخصية
        }

        $personalPhotoPath = null;
        if ($request->hasFile('personal_photo')) {
            $personalPhotoPath = $request->file('personal_photo')->store('personal', 'public'); // تخزين الصورة الشخصية
        }

        $idImagePath = null;
        if ($request->hasFile('id_photo')) {
            $idImagePath = $request->file('id_photo')->store('identities', 'public'); // تخزين صورة الهوية
        }

        // إنشاء مستخدم جديد
        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10), // صلاحية الرمز 10 دقايق
            'is_verified' => false
        ]);
        // إنشاء بروفايل للمستخدم
        Profile::create([
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'birth_date' => $birthDate,
            'city_id' => $request->city_id,
            'area_id' => $request->area_id,
            'profile_photo' => $ProfilePhotoPath,
            'personal_photo' => $personalPhotoPath,
            'id_photo' => $idImagePath
        ]);

        // إنشاء توكن للمستخدم
        $token = $user->createToken('auth_token')->plainTextToken;

        // // إعادة استجابة بنجاح التسجيل
        // return response()->json([
        //     'message' => 'the account created successfully, Please verify your phone number.',
        //     'otp'     => $otp,
        //     'phone_number'   => $request->phone_number,
        //     'next_step' => 'verify-otp',
        //     'access_token' => $token,
        //     'token_type' => 'Bearer',
        // ]);

        return response()->json([
            'message' => __('api.account_created_success'),
            'otp'     => $otp,
            'phone_number'   => $request->phone_number,
            'next_step' => 'verify-otp',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }




    // التحقق من رمز التحقق
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|digits_between:9,15',
            'otp'   => 'required|string|max:255'
        ]);

        $user = User::where('phone_number', $request->phone_number)->firstOrFail();
        if ($user->otp_code !== $request->otp || Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json([
                // 'message' => 'the code isnot true', 
                'message' => __('api.invalid_otp'),
                'otp' => $user->otp_code
            ], 422);
        }

        // إذا كان الحساب لسا ما تمت الموافقة عليه من المدير
        if (!$user->is_verified) {
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();
            return response()->json([
                // 'message' => 'waiting the admin to approve the account.',
                'message' => __('api.pending_admin_approval'),
                'status'  => 'pending_approval'
            ]);
        }
    }

    // إعادة إرسال رمز التحقق
    //ممكن نعمل حد اقصى لاعادة الارسال
    //ممكن نعمل الدالة تشتغل عن طريق التوكن بدل رقم الهاتف
    public function resendOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|digits_between:9,15'
        ]);

        $user = User::where('phone_number', $request->phone_number)->firstOrFail();

        $otp = rand(1000, 9999);

        session(['otp' => $otp]);
        session(['otp_phone' => $request->phone_number]);

        // أرسل عبر واتساب
        sendWhatsAppMessage(
            $request->phone_number,
            "Your confirmation code is: {$otp}. Do not share it with anyone."
        );

        $user->otp_code = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        return response()->json([
            // 'message' => 'OTP resent successfully.',
            'message' => __('api.otp_resent_success'),
            'otp'     => $otp
        ]);
    }

    // تسجيل الدخول
    // يمكن تسجيل الدخول برقم الهاتف وكلمة المرور
    // أو برقم الهاتف فقط إذا كان الحساب موثق ومفعل
    // ممكن نضيف تحقق ثنائي في المستقبل
    // ممكن نضيف تقييد لعدد محاولات الدخول الفاشلة
    // ممكن نضيف ميزة "تذكرني" في المستقبل
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'phone_number' => 'required|digits_between:9,15',
    //         'password' => 'required|string|min:6|max:255'
    //     ]);

    //     // التحقق من بيانات الدخول
    //     if (!Auth::attempt($request->only('phone_number', 'password'))) {
    //         return response()->json([
    //             'message' => 'Invalid login details'
    //         ], 401);
    //     }
    //     // إنشاء توكن جديد للمستخدم
    //     $user = User::where('phone_number', $request->phone_number)->first();
    //     $token = $user->createToken('auth_Token')->plainTextToken;

    //     // إعادة استجابة بنجاح الدخول
    //     return response()->json([
    //         'message' => 'Login successful',
    //         'user' => FacadesAuth::user(),
    //         'Token' => $token
    //     ], 201);
    // }
    public function login(Request $request)
    {
        $user = User::where('phone_number', $request->phone_number)->first();
        if (!$user->is_verified) {
            return response()->json(
                ['message' => __('api.pending_admin_approval')]
                );
        }

        $request->validate([
            'phone_number' => 'required|digits_between:9,15',
            'password'     => 'required|string|min:6|max:255'
        ]);

        if (!Auth::attempt($request->only('phone_number', 'password'))) {
            return response()->json([
                // 'message' => 'Invalid login details'
                'message' => __('api.invalid_login')
            ], 401);
        }

        // إنشاء التوكن
        $token = $user->createToken('auth_token')->plainTextToken;

        // جلب الـ city_id من العلاقة profile
        $city_id = $user->profile?->city_id;

        return response()->json([
            // 'message'      => 'Login successful',
            'message' => __('api.login_success'),
            'user'         => [
                'id'            => $user->id,
                'phone_number'  => $user->phone_number,
                'name'          => $user->name,
                'is_verified'   => $user->is_verified,
                'first_name'   => $user->profile?->first_name,
                'last_name'    => $user->profile?->last_name,
                'city_id'      => $city_id,
            ],
            // 'profile' => [
            //     'first_name'   => $user->profile?->first_name,
            //     'last_name'    => $user->profile?->last_name,
            //     'city_id'      => $city_id,
            //     // 'area_id'      => $user->profile?->area_id,
            // ],
            'Token'        => $token,
            // 'token_type'   => 'Bearer',
        ], 200);
    }

    // تسجيل الخروج
    // حذف التوكن الحالي
    // يمكن إضافة ميزة تسجيل الخروج من جميع الأجهزة في المستقبل
    // ممكن نضيف سجل نشاط المستخدم في المستقبل
    public function logout(Request $request)
    {
        // حذف التوكن الحالي
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            // 'message' => 'Logout successful'
            'message' => __('api.logout_success')
        ], 204);
    }
}
