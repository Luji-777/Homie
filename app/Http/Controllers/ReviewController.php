<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Http\Request;

class ReviewController extends Controller
{


    public function store(Request $request)
    {
        // 1. التحقق من البيانات الواردة
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'booking_id'   => 'required|exists:bookings,id',
            'rating'       => 'required|numeric|min:1|max:5',
            'comment'      => 'nullable|string|max:1000',
        ]);

        // 2. جلب اليوزر المسجل دخول
        $user = FacadesAuth::user();

        // 3. جلب الحجز والتحقق من صحته
        $booking = Booking::findOrFail($validated['booking_id']);

        // تحقق إنو الحجز يخص اليوزر الحالي
        if ($booking->tenant_id !== $user->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'لا يمكنك تقييم حجز لا يخصك'
            ], 403);
        }

        // تحقق إنو الشقة في الحجز مطابقة للي جاي في الطلب
        if ($booking->apartment_id != $validated['apartment_id']) {
            return response()->json([
                'status'  => 'error',
                'message' => 'الحجز لا يتطابق مع الشقة المطلوبة'
            ], 400);
        }

        // تحقق إنو الحجز منتهي (غيّر الشرط حسب حالات الحجز عندك)
        // مثال: إذا عندك عمود status في bookings
        if ($booking->status !== 'completed') {
            return response()->json([
                'status'  => 'error',
                'message' => 'لا يمكن تقييم الحجز إلا بعد انتهائه'
            ], 400);
        }

        // 4. منع التكرار: ما يقدرش يضيف تقييم مرتين لنفس الشقة
        $existingReview = Review::where('apartment_id', $validated['apartment_id'])
            ->where('tenant_id', $user->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'status'  => 'error',
                'message' => 'لقد قيّمت هذه الشقة مسبقًا'
            ], 400);
        }

        // 5. حفظ التقييم الجديد
        $review = Review::create([
            'apartment_id' => $validated['apartment_id'],
            'tenant_id'    => $user->id,
            'booking_id'   => $validated['booking_id'],
            'rating'       => $validated['rating'],
            'comment'      => $validated['comment'],
        ]);

        // 6. رد ناجح
        return response()->json([
            'status'  => 'success',
            'message' => 'تم إضافة التقييم بنجاح',
            'data'    => [
                'review' => [
                    'id'           => $review->id,
                    'rating'       => (float) $review->rating,
                    'comment'      => $review->comment,
                    'created_at'   => $review->created_at->format('Y-m-d'),
                ]
            ]
        ], 201);
    }



    public static function formatForApartment(int $apartmentId)
    {
        $reviews = Review::where('apartment_id', $apartmentId)
            ->with(['tenant', 'tenant.profile'])
            ->get();

        $averageRating = $reviews->avg('rating') ?? 0;
        $totalReviews  = $reviews->count();

        $formattedReviews = $reviews->map(function ($review) {
            return [
                'id'         => $review->id,
                'rating'     => (float) $review->rating,
                'comment'    => $review->comment ?? '',
                'created_at' => $review->created_at->format('Y-m-d'),
                'reviewer'   => [
                    'id'            => $review->tenant->id ?? null,
                    'full_name'     => $review->tenant->name ?? 'مستخدم مجهول',
                    'profile_image' => $review->tenant->profile->profile_photo ?? null,
                ]
            ];
        });

        return [
            'summary' => [
                'average_rating' => round($averageRating, 1),
                'total_reviews'  => $totalReviews
            ],
            'data' => $formattedReviews
        ];
    }

    // الدالة القديمة show الخاصة بالـ API
    public function show(int $apartmentId)
    {
        return response()->json([
            'status' => 'success',
            ReviewController::formatForApartment($apartmentId)
        ]);
    }




    public function destroy(int $id)
    {
        // التحقق من تسجيل الدخول
        $user = FacadesAuth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'يجب تسجيل الدخول أولاً'
            ], 401);
        }

        // حذف تقييم من قبل صاحب التقييم فقط
        $review = Review::findOrFail($id);

        if ($review->tenant_id !== $user->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'لا يمكنك حذف تقييم لا يخصك'
            ], 403);
        }

        $review->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'تم حذف التقييم بنجاح'
        ]);
    }
}
