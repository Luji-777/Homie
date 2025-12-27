<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApartmentRequest;
use App\Http\Requests\UpdateApartmentRequest;
use App\Http\Requests\ApartmentFilterRequest;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;

use Illuminate\Support\Facades\Storage;


class ApartmentController extends Controller
{

    // Display a listing of the resource.
    public function index()
    {
        $apartments = FacadesAuth::user()->apartments()->with(['area.city'])->get();
        return response()->json($apartments, 201);
    }



    // Store a newly created resource in storage.
    public function store(StoreApartmentRequest $request)
    {
        $validatedData = $request->validated();

        // استخراج الصور ومؤشر الغلاف قبل الحذف
        $images = $request->file('images') ?? []; // أفضل نجيب الملفات مباشرة من $request->file
        $coverIndex = $request->input('cover_index', 0); // افتراضي 0 إذا ما أرسل

        // إزالة الحقول اللي ما بدنا ندخلها في جدول apartments
        unset($validatedData['images'], $validatedData['cover_index']);

        $owner_id = FacadesAuth::user()->id;
        $validatedData['owner_id'] = $owner_id;
        $validatedData['is_approved'] = false; // By default, set is_approved to false
        $apartment = Apartment::create($validatedData); // Create the apartment

        // تحميل الصور إذا موجودة وغير فارغة
        if (!empty($images)) {
            $this->uploadImages($apartment, $images, (int)$coverIndex);
        }

        // تحميل العلاقات مع الصور والغلاف
        $apartment->load(['area.city', 'apartment_image', 'isCover']);




        return response()->json([
            'message' => 'Apartment created successfuly. waiting for admin to approve.',
            'apartment' => $apartment
        ], 201);
    }



    // Display the specified resource.
    public function show(int $id)
    {
        $apartment = Apartment::with([
            'owner.profile',
            'area.city',
            'apartment_image',
            'isCover',
            'review.tenant'  // جديد
        ])->findOrFail($id);


        // تفاصيل المالك
        $apartment->owner = $apartment->owner()->select('id', 'name', 'phone_number')->first();
        // تفاصيل البروفايل
        $apartment->owner->profile = $apartment->owner->profile()->select('first_name', 'last_name', 'profile_photo')->first();


        return response()->json([
            'status' => $apartment->status,
            'data' => [
                'apartment' => [
                    'id' => $apartment->id,
                    'type' => ucfirst($apartment->type),
                    'title' => $apartment->title,
                    'description' => $apartment->discription,
                    'rent_price' => $apartment->price_per_month,
                    'rent_type' => 'monthly',
                    'images' => $apartment->apartment_image->map(function ($image) {
                        return asset('storage/' . $image->image_path);
                    })->toArray(),
                    'address' => [
                        'city_name' => $apartment->area->city->name,
                        'area_name' => $apartment->area->name,
                        'detailed_address' => $apartment->address,
                    ],
                    'amenities' => [
                        'bedrooms'   => $apartment->bedrooms,
                        'bathrooms'  => $apartment->bathrooms,
                        'space'      => (float) $apartment->space,
                        'floor'      => $apartment->floor,
                        'has_wifi'   => (bool) $apartment->wifi,
                        'has_solar'  => (bool) $apartment->solar,
                    ],
                    'owner' => [
                        'id'             => $apartment->owner->id,
                        'full_name'      => $apartment->owner->profile->first_name . ' ' . $apartment->owner->profile->last_name,
                        'phone_number'   => $apartment->owner->phone_number ?? null,
                        // 'bio'            => $apartment->owner->bio ?? null,    // إذا عندك عمود bio في users
                        'profile_image'  => $apartment->owner->profile->profile_photo ?? null,
                    ],
                    'reviews' => $apartment->review->map(function ($review) {
                        return [
                            'user_name'    => $review->tenant->name ?? 'مستخدم مجهول',
                            'user_image'   => $review->tenant->profile->profile_photo ?? null, // غيّر profile_image لاسم العمود الصحيح عندك في users (مثل avatar أو photo)
                            'comment'      => $review->comment ?? '',
                            'rating_value' => (float) $review->rating,
                            'created_at'   => $review->created_at->format('Y-m-d'),
                        ];
                    })->toArray(),
                ]
            ]
        ], 200);
    }



    // Update the specified resource in storage.
    public function update(UpdateApartmentRequest $request, int $id)
    {
        // $owner_id = Auth::user()->id; // أصلحت FacadesAuth → Auth
        $owner_id = FacadesAuth::user()->id;


        $apartment = Apartment::findOrFail($id);

        if ($apartment->owner_id !== $owner_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // جلب البيانات المصادق عليها (للحقول النصية فقط)
        $validatedData = $request->validated();

        // استخراج الملفات و cover_index مباشرة من الـ request (مش من validated)
        $newImages = $request->file('images'); // هنا المفتاح: file() مش input()
        $coverIndex = $request->input('cover_index'); // هنا ممكن يكون null

        // إزالة الحقول اللي ما بدنا نحفظها في جدول apartments (لو كانت موجودة)
        unset($validatedData['images'], $validatedData['cover_index']);

        // تحديث الحقول النصية
        $apartment->update($validatedData);

        $kk = 0;

        // 1. إذا وجدت صور جديدة
        if ($request->hasFile('images')) {  // أفضل من !empty($newImages)
            $kk = 111;

            // حذف الملفات القديمة من التخزين
            foreach ($apartment->apartment_image as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            // حذف السجلات من قاعدة البيانات
            $apartment->apartment_image()->delete();

            // رفع الصور الجديدة
            $this->uploadImages($apartment, $newImages, $coverIndex ?? 0);
        }
        // 2. إذا ما في صور جديدة لكن في cover_index
        elseif ($coverIndex !== null) {
            $kk = 222;
            $this->setCoverImage($apartment, (int)$coverIndex);
        }
        // 3. ما في تغيير على الصور

        $apartment->load(['area.city', 'apartment_image', 'isCover']);

        return response()->json([
            'message'   => 'Apartment updated successfully.',
            'apartment' => $apartment,
            'kk'        => $kk,
            'debug'     => [  // مؤقت للتصحيح
                'has_images' => $request->hasFile('images'),
                'cover_index' => $coverIndex,
                'images_count' => $newImages ? count($newImages) : 0
            ]
        ], 200);
    }



    // Remove the specified resource from storage.
    public function destroy(int $id)
    {
        $owner_id = FacadesAuth::user()->id;
        $apartments = Apartment::findorfail($id);
        if ($apartments->owner_id != $owner_id) {
            return response()->json([
                'message' => 'Unauthorize'
            ], 403);
        }
        $apartments->delete();

        return response()->json([
            'message' => 'the apartment deleted successfuly.'
        ], 200);
    }



    public function filter(ApartmentFilterRequest $request)
    {

        $query = Apartment::query()
            ->where('is_approved', false) // فقط الشقق المعتمدة يعني لازم نحطها ترووو بس مشان التجريب حاليا
            ->with(['area.city']); // لإرجاع اسم المنطقة والمحافظة مع الشقة


        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // فلتر حسب نوع الإيجار (rent_type)
        if ($request->filled('rent_type')) {
            if ($request->rent_type === 'day') {
                // فلتر حسب السعر اليومي
                if ($request->filled('price_min')) {
                    $query->where('price_per_day', '>=', $request->price_min);
                }
                if ($request->filled('price_max')) {
                    $query->where('price_per_day', '<=', $request->price_max);
                }
            } elseif ($request->rent_type === 'month') {
                // فلتر حسب السعر الشهري
                if ($request->filled('price_min')) {
                    $query->where('price_per_month', '>=', $request->price_min);
                }
                if ($request->filled('price_max')) {
                    $query->where('price_per_month', '<=', $request->price_max);
                }
            }
        }


        // فلتر حسب المحافظة (city_id)
        if ($request->filled('city_id')) {
            $query->whereHas('area', function ($q) use ($request) {
                $q->where('city_id', $request->city_id);
            });
        }

        // فلتر حسب المنطقة (area_id)
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }



        // فلتر حسب عدد الغرف
        if ($request->filled('rooms')) {
            $query->where('rooms', $request->rooms);
        }

        // فلتر حسب وجود WiFi
        if ($request->filled('wifi')) {
            $Wifi = filter_var($request->wifi, FILTER_VALIDATE_BOOLEAN);
            $query->where('wifi', $Wifi);
        }

        // فلتر حسب وجود سولار
        if ($request->filled('solar')) {
            $Solar = filter_var($request->solar, FILTER_VALIDATE_BOOLEAN);
            $query->where('solar', $Solar);
        }



        // ترتيب حسب الأحدث أولاً (اختياري)
        $apartments = $query->latest()->paginate(12); // 12 شقة في الصفحة، غيّر الرقم كيف ما بدك
        return response()->json([
            'message' => 'Apartments retrieved successfully.',
            'data'    => $apartments,
            'filters' => $request->only(['type', 'rent_type', 'city_id', 'area_id', 'price_min', 'price_max', 'rooms', 'wifi'])
        ], 200);
    }



    // دالة مساعدة لتعيين صورة الغلاف
    private function setCoverImage(Apartment $apartment, int $coverIndex)
    {
        $images = $apartment->apartment_image;

        if ($images->isEmpty()) {
            return; // لا صور لتحديد غلاف
        }

        // إلغاء الغلاف الحالي
        $images->each(function ($image) {
            $image->is_cover = false;
            $image->save();
        });

        // تحديد الغلاف الجديد
        if (isset($images[$coverIndex])) {
            $images[$coverIndex]->is_cover = true;
            $images[$coverIndex]->save();
        } else {
            // إذا كان المؤشر خارج النطاق → اجعل الصورة الأولى غلافًا
            $images->first()->update(['is_cover' => true]);
        }
    }

    // دالة مساعدة لتحميل الصور
    private function uploadImages(Apartment $apartment, array $images, int $coverIndex = 0): void
    {
        if (empty($images)) {
            return;
        }

        // أولاً: نعمل reset لكل الصور (نخلي الكل is_cover = false)
        $apartment->apartment_image()->update(['is_cover' => false]);

        foreach ($images as $index => $image) {
            if (!$image->isValid()) {
                continue;
            }

            $path = $image->store('apartments', 'public');

            $apartment->apartment_image()->create([
                'image_path' => $path,
                'is_cover'   => ($index === $coverIndex)
                // 'sort_order' => $index,
            ]);
        }
    }



























































public function favorites(int $id)
    {
        $apartment = Apartment::with(['favorites'])->findOrFail($id);

        return response()->json([
            'message' => 'Apartment favorites retrieved successfully.',
            'data' => $apartment->favorites
        ], 200);
    }

    public function favoriteCount(int $id)
    {
        $apartment = Apartment::findOrFail($id);
        $favoriteCount = $apartment->favorites()->count();

        return response()->json([
            'message' => 'Apartment favorite count retrieved successfully.',
            'data' => $favoriteCount
        ], 200);
    }

    public function setFavoriteApartment(int $id){

    }

    /////////////////////////////////////////////////////////////////////////////////////

    public function apartmentDetails($id)
    {
        $apartment = Apartment::with(['area.city', 'apartment_image', 'isCover'])->findOrFail($id);

        return response()->json([
            'message' => 'Apartment details retrieved successfully.',
            'data' => $apartment
        ], 200);
    }





    // Get all reviews for a specific apartment
    public function reviews($id)
    {
        $apartment = Apartment::with(['reviews'])->findOrFail($id);

        return response()->json([
            'message' => 'Apartment reviews retrieved successfully.',
            'data' => $apartment->reviews
        ], 200);
    }




    // Get total reviews and average rating for a specific apartment
    public function apartmentRatingsSummary($id)
    {
        $apartment = Apartment::findOrFail($id);
        $totalReviews = $apartment->reviews()->count();
        $averageRating = $apartment->reviews()->avg('rating');

        return response()->json([
            'message' => 'Apartment ratings summary retrieved successfully.',
            'data' => [
                'total_reviews' => $totalReviews,
                'average_rating' => $averageRating
            ]
        ], 200);
    }


    // Check if a tenant has reviewed a specific apartment
    public function hasTenantReviewed($apartment_id, $tenant_id)
    {
        $apartment = Apartment::findOrFail($apartment_id);
        $hasReviewed = $apartment->reviews()->where('tenant_id', $tenant_id)->exists();

        return response()->json([
            'message' => 'Tenant review status retrieved successfully.',
            'data' => $hasReviewed
        ], 200);
    }



    // Tenant submits a review for an apartment
    public function reviewApartment(Request $request, $apartment_id)
    {
        $tenant_id = FacadesAuth::user()->id;

        // تحقق مما إذا كان المستأجر قد قام بمراجعة هذه الشقة بالفعل
        $apartment = Apartment::findOrFail($apartment_id);
        // تحقق اذا كان المستخدم قد قام بحجز هذه الشقة
        $hasBooked = Booking::where('apartment_id', $apartment_id)->where('tenant_id', $tenant_id)->exists();

        if (!$hasBooked) {
            return response()->json([
                'message' => 'You have not booked this apartment.'
            ], 400);
        }

        $hasReviewed = $apartment->reviews()->where('tenant_id', $tenant_id)->exists();

        if ($hasReviewed) {
            return response()->json([
                'message' => 'You have already reviewed this apartment.'
            ], 400);
        }

        // إنشاء المراجعة الجديدة
        $validatedData = $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string',
            'booking_id' => 'required|exists:bookings,id'
        ]);

        $review = Review::create([
            'apartment_id' => $apartment_id,
            'tenant_id' => $tenant_id,
            'booking_id' => $validatedData['booking_id'],
            'rating' => $validatedData['rating'],
            'comment' => $validatedData['comment'] ?? null
        ]);

        return response()->json([
            'message' => 'Review submitted successfully.',
            'data' => $review
        ], 201);
    }

    public function bookedApartments()
    {
        $tenant_id = FacadesAuth::user()->id;

        $bookedApartments = Apartment::whereHas('bookings', function ($query) use ($tenant_id) {
            $query->where('tenant_id', $tenant_id);
        })->with(['area.city', 'apartment_image', 'isCover'])->get();

        return response()->json([
            'message' => 'Booked apartments retrieved successfully.',
            'data' => $bookedApartments
        ], 200);
    }








    public function availableApartments()
    {
        $tenant_id = FacadesAuth::user()->id;

        $availableApartments = Apartment::whereDoesntHave('bookings', function ($query) use ($tenant_id) {
            $query->where('tenant_id', $tenant_id);
        })->with(['area.city', 'apartment_image', 'isCover'])->get();

        return response()->json([
            'message' => 'Available apartments retrieved successfully.',
            'data' => $availableApartments
        ], 200);
    }

    public function rentedApartments()
    {
        $tenant_id = FacadesAuth::user()->id;

        $rentedApartments = Apartment::whereHas('bookings', function ($query) use ($tenant_id) {
            $query->where('tenant_id', $tenant_id)
                ->where('status', 'completed'); // فقط الإيجارات المكتملة
        })->with(['area.city', 'apartment_image', 'isCover'])->get();

        return response()->json([
            'message' => 'Rented apartments retrieved successfully.',
            'data' => $rentedApartments
        ], 200);
    }

    public function myApartments()
    {
        $owner_id = FacadesAuth::user()->id;

        $myApartments = Apartment::where('owner_id', $owner_id)
            ->with(['area.city', 'apartment_image', 'isCover'])
            ->get();

        return response()->json([
            'message' => 'My apartments retrieved successfully.',
            'data' => $myApartments
        ], 200);
    }

    public  function myApprovedApartments()
    {
        $owner_id = FacadesAuth::user()->id;

        $myApprovedApartments = Apartment::where('owner_id', $owner_id)
            ->where('is_approved', true)
            ->with(['area.city', 'apartment_image', 'isCover'])
            ->get();

        return response()->json([
            'message' => 'My approved apartments retrieved successfully.',
            'data' => $myApprovedApartments
        ], 200);
    }


    /*
//admin functions 
*/

    // approve apartment (admin only)
    public function approveApartment($id)
    {
        $apartment = Apartment::findOrFail($id);
        $apartment->is_approved = true;
        $apartment->save();

        return response()->json([
            'message' => 'Apartment approved successfully.',
            'apartment' => $apartment
        ], 200);
    }



    // reject apartment (admin only)
    public function rejectApartment($id)
    {
        $apartment = Apartment::findOrFail($id);
        $apartment->is_approved = false;
        $apartment->save();

        return response()->json([
            'message' => 'Apartment rejected successfully.',
            'apartment' => $apartment
        ], 200);
    }



    // get all apartments (for admin)
    public function allApartments()
    {
        $apartments = Apartment::with(['area.city'])->get();

        return response()->json([
            'message' => 'All apartments retrieved successfully.',
            'data' => $apartments
        ], 200);
    }



    // get all pending apartments (for admin)
    public function pendingApartments()
    {
        $apartments = Apartment::where('is_approved', false)->with(['area.city'])->get();

        return response()->json([
            'message' => 'Pending apartments retrieved successfully.',
            'data' => $apartments
        ], 200);
    }



    // get all approved apartments (for admin)
    public function approvedApartments()
    {
        $apartments = Apartment::where('is_approved', true)->with(['area.city'])->get();

        return response()->json([
            'message' => 'Approved apartments retrieved successfully.',
            'data' => $apartments
        ], 200);
    }
}
