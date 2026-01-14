<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Apartment;
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
                // 'message' => 'لا يمكنك تقييم حجز لا يخصك'
                'message' => __('api.unauthorized_action')
            ], 403);
        }

        // تحقق إنو الشقة في الحجز مطابقة للي جاي في الطلب
        if ($booking->apartment_id != $validated['apartment_id']) {
            return response()->json([
                'status'  => 'error',
                // 'message' => 'الحجز لا يتطابق مع الشقة المطلوبة'
                'message' => __('api.invalid_booking_apartment')
            ], 400);
        }

        // تحقق إنو الحجز منتهي (غيّر الشرط حسب حالات الحجز عندك)
        // مثال: إذا عندك عمود status في bookings
        if ($booking->status !== 'completed') {
            return response()->json([
                'status'  => 'error',
                // 'message' => 'لا يمكن تقييم الحجز إلا بعد انتهائه'
                'message' => __('api.review_not_allowed')
            ], 400);
        }

        // 4. منع التكرار: ما يقدرش يضيف تقييم مرتين لنفس الشقة
        $existingReview = Review::where('apartment_id', $validated['apartment_id'])
            ->where('tenant_id', $user->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'status'  => 'error',
                // 'message' => 'لقد قيّمت هذه الشقة مسبقًا'
                'message' => __('api.review_already_exists')
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
            // 'message' => 'تم إضافة التقييم بنجاح',
            'message' => __('api.review_created_success'),
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
                // 'message' => 'يجب تسجيل الدخول أولاً'
                'message' => __('api.unauthorized_action')
            ], 401);
        }

        // حذف تقييم من قبل صاحب التقييم فقط
        $review = Review::findOrFail($id);

        if ($review->tenant_id !== $user->id) {
            return response()->json([
                'status'  => 'error',
                // 'message' => 'لا يمكنك حذف تقييم لا يخصك'
                'message' => __('api.cannot_delete_review_unauthorized')
            ], 403);
        }

        $review->delete();

        return response()->json([
            'status'  => 'success',
            // 'message' => 'تم حذف التقييم بنجاح'
            'message' => __('api.review_deleted_success')
        ]);
    }



    public function topRated(Request $request)
    {
        $limit = $request->get('limit', 10);

        $apartments = Apartment::with([
            'owner.profile',
            'area.city',
            'isCover'
        ])
            ->withAvg('review', 'rating')
            ->withCount('review')
            ->orderByDesc('review_avg_rating')
            ->take($limit)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $apartments->map(function ($apartment) {
                return ApartmentController::format($apartment);
            })
        ]);
    }



    // public function topRatedApartments(Request $request)
    // {
    //     $perPage = $request->get('per_page', 10); // عدد العناصر بالصفحة

    //     $apartments = Apartment::with([
    //         'owner.profile',
    //         'area.city',
    //         'isCover'
    //     ])
    //         ->withAvg('review', 'rating')
    //         ->withCount('review')
    //         ->orderByDesc('review_avg_rating')
    //         ->paginate($perPage);

    //     $data = $apartments->getCollection()->map(function ($apartment) {
    //         return [
    //             'id' => $apartment->id,
    //             'title' => $apartment->title,
    //             'price' => $apartment->price_per_month,
    //             'cover_image' => $apartment->isCover
    //                 ? asset('storage/' . $apartment->isCover->image_path)
    //                 : null,
    //             'space' => (float) $apartment->space,
    //             'bedrooms' => $apartment->bedrooms,
    //             'bathrooms' => $apartment->bathrooms,
    //             'address' => $apartment->area->city->name . '، ' . $apartment->area->name,
    //             'rental_type' => 'monthly',
    //             'apartment_type' => $apartment->type,
    //             'review' => [
    //                 'average_rating' => round($apartment->review_avg_rating ?? 0, 1),
    //                 'total_reviews'  => $apartment->review_count
    //             ],
    //             'owner' => [
    //                 'id' => $apartment->owner->id,
    //                 'full_name' => $apartment->owner->profile->first_name . ' ' . $apartment->owner->profile->last_name,
    //                 'profile_image' => $apartment->owner->profile->profile_photo ?? null,
    //                 'phone_number' => $apartment->owner->phone_number ?? null,
    //             ],
    //         ];
    //     });

    //     return response()->json([
    //         'status' => 'success',
    //         'pagination' => [
    //             'current_page' => $apartments->currentPage(),
    //             'last_page'    => $apartments->lastPage(),
    //             'per_page'     => $apartments->perPage(),
    //             'total'        => $apartments->total(),
    //         ],
    //         'data' => $data
    //     ]);
    // }




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
}
