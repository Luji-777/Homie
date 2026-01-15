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

    // شقق المستخدم
    public function index()
    {
        $apartments = FacadesAuth::user()->apartments()->with(['area.city'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $apartments->map(
                fn($apt) => ApartmentController::format($apt)
            )
        ]);
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
            // 'message' => 'Apartment created successfuly. waiting for admin to approve.',
            'message' => __('api.apartment_created'),
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
            'status' => 'success',
            'data' => ApartmentController::format($apartment)
        ]);
    }



    // Update the specified resource in storage.
    public function update(UpdateApartmentRequest $request, int $id)
    {
        // $owner_id = Auth::user()->id; // أصلحت FacadesAuth → Auth
        $owner_id = FacadesAuth::user()->id;


        $apartment = Apartment::findOrFail($id);

        if ($apartment->owner_id !== $owner_id) {
            return response()->json([
                // 'message' => 'Unauthorized'
                'message' => __('api.unauthorized')

            ], 403);
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
            // 'message'   => 'Apartment updated successfully.',
            'message'   => __('api.apartment_updated'),
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
                // 'message' => 'Unauthorize'
                'message' => __('api.unauthorized')
            ], 403);
        }
        $apartments->delete();

        return response()->json([
            // 'message' => 'the apartment deleted successfuly.'
            'message' => __('api.apartment_deleted')
        ], 200);
    }




    public function filter(ApartmentFilterRequest $request)
    {
        $query = Apartment::query()
            ->where('is_approved', true)
            ->with(['area.city']);

        // ------------------ فلتر النوع (type) - يدعم مصفوفة ------------------
        if ($request->filled('type')) {
            // إذا أرسل string واحد فقط، نحوله إلى array
            $types = is_array($request->type) ? $request->type : [$request->type];

            $query->whereIn('type', $types);
        }

        // فلتر حسب نوع الإيجار (rent_type)
        if ($request->filled('rent_type')) {
            $query->where('rent_type', $request->rent_type);
        }
        // حسب السعر min و max
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // if ($request->filled('rent_type')) {
        //     if ($request->rent_type === 'day') {
        //         if ($request->filled('price_min')) {
        //             $query->where('price_per_day', '>=', $request->price_min);
        //         }
        //         if ($request->filled('price_max')) {
        //             $query->where('price_per_day', '<=', $request->price_max);
        //         }
        //     } elseif ($request->rent_type === 'month') {
        //         if ($request->filled('price_min')) {
        //             $query->where('price_per_month', '>=', $request->price_min);
        //         }
        //         if ($request->filled('price_max')) {
        //             $query->where('price_per_month', '<=', $request->price_max);
        //         }
        //     }
        // }

        // فلتر المحافظة
        if ($request->filled('city_id')) {
            $query->whereHas('area', function ($q) use ($request) {
                $q->where('city_id', $request->city_id);
            });
        }

        // فلتر المنطقة
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        // عدد الغرف
        if ($request->filled('rooms')) {
            $query->where('rooms', $request->rooms);
        }

        // Wifi
        if ($request->filled('wifi')) {
            $wifi = filter_var($request->wifi, FILTER_VALIDATE_BOOLEAN);
            $query->where('wifi', $wifi);
        }

        // Solar
        if ($request->filled('solar')) {
            $solar = filter_var($request->solar, FILTER_VALIDATE_BOOLEAN);
            $query->where('solar', $solar);
        }

        $apartments = $query->latest()->paginate(12);


        
        $formatted = $apartments->map(function ($apartment) {
            $user = FacadesAuth::user();
            $averageRating = $apartment->review->avg('rating');
            // return [
            //     'apartment' => [
            //         'id' => $apartment->id,
            //         // 'type' => ucfirst($apartment->type),
            //         'type' => __('api.type_' . $apartment->type),

            //         'title' => $apartment->title,
            //         'discription' => $apartment->discription,
            //         'rent_price' => $apartment->price,
            //         // 'rent_type' => $apartment->rent_type,
            //         'rent_type' => __('api.rent_' . $apartment->rent_type),

            //         'images' => $apartment->apartment_image->map(
            //             fn($img) =>
            //             asset('storage/' . $img->image_path)
            //         )->toArray(),

            //         'address' => [
            //             // 'city_name' => $apartment->area->city->name ?? null,
            //             // 'area_name' => $apartment->area->name ?? null,
            //             'city_name' => __('cities.' . ($apartment->area->city->name ?? '')),
            //             'area_name' => __('areas.' . ($apartment->area->name ?? '')),
            //             'detailed_address' => $apartment->address,
            //         ],

            //         'amenities' => [
            //             'bedrooms'   => $apartment->bedrooms,
            //             'bathrooms'  => $apartment->bathrooms,
            //             'space'      => (float) $apartment->space,
            //             'floor'      => $apartment->floor,
            //             'has_wifi'   => (bool) $apartment->wifi,
            //             'has_solar'  => (bool) $apartment->solar,
            //         ],
            //     ],
            //     'owner' => [
            //         'id' => $apartment->owner->id,
            //         'full_name' => ($apartment->owner->profile->first_name ?? '') . ' ' .
            //             ($apartment->owner->profile->last_name ?? ''),
            //         'phone_number' => $apartment->owner->phone_number ?? null,
            //         'profile_image' => $apartment->owner->profile->profile_photo ?? null,
            //     ],

            //     'reviews' => ReviewController::formatForApartment($apartment->id),
            //     'isFavorite' => FavoriteController::isFavorite($apartment->id),
            //     'isOwner' => $user ? $apartment->owner_id === $user->id : false,
            // ];

            return [
                'apartment' => [
                    'id'             => $apartment->id,
                    'title'          => $apartment->title,
                    'price'          => $apartment->price,
                    'cover_image'    => $apartment->isCover
                        ? asset('storage/' . $apartment->isCover->image_path)
                        : null,
                    'space'          => (float) $apartment->space,
                    'bedrooms'       => $apartment->bedrooms,
                    'bathrooms'      => $apartment->bathrooms,
                    'rooms'          => $apartment->rooms ?? null,
                    // 'address'        => $apartment->area->city->name . '، ' . $apartment->area->name,
                    'address' => __('cities.' . ($apartment->area->city->name ?? ''))
                        . '، ' .
                        __('areas.' . ($apartment->area->name ?? '')),


                    // 'rental_type'    => $apartment->rent_type,
                    'rental_type' => __('api.rent_' . $apartment->rent_type),

                    // 'apartment_type' => $apartment->type,
                    'apartment_type' => __('api.type_' . $apartment->type),
                    'average_rating'         => round($averageRating, 1),
                    'isFavorite' => FavoriteController::isFavorite($apartment->id),

                ],
                'owner' => [
                    'id'            => $apartment->owner->id,
                    'profile_image' => $apartment->owner->profile->profile_photo ?? null,
                    'full_name'     => $apartment->owner->profile->first_name . ' ' . $apartment->owner->profile->last_name,
                    'phone_number'  => $apartment->owner->phone_number,
                    'bio'           => null, // مؤقتاً
                ]
            ];
        });


        return response()->json([
            // 'message' => 'Apartments retrieved successfully.',
            'message' => __('api.apartments_retrieved'),
            'data' => $formatted,
            'filters' => $request->only([
                'type',
                'rent_type',
                'city_id',
                'area_id',
                'price_min',
                'price_max',
                'rooms',
                'wifi',
                'solar'
            ]),
            'pagination' => [
                'current_page' => $apartments->currentPage(),
                'last_page' => $apartments->lastPage(),
                'per_page' => $apartments->perPage(),
                'total' => $apartments->total(),
            ]
        ], 200);
    }



    public function approvedApartments()
    {
        $apartments = Apartment::where('is_approved', true)
            ->with(['area.city', 'isCover', 'owner'])  // ← لازم owner
            ->get();

        $formatted = $apartments->map(function ($apartment) {
            $averageRating = $apartment->review->avg('rating');

            return [
                'apartment' => [
                    'id'             => $apartment->id,
                    'title'          => $apartment->title,
                    'price'          => $apartment->price,
                    'cover_image'    => $apartment->isCover
                        ? asset('storage/' . $apartment->isCover->image_path)
                        : null,
                    'space'          => (float) $apartment->space,
                    'bedrooms'       => $apartment->bedrooms,
                    'bathrooms'      => $apartment->bathrooms,
                    'rooms'          => $apartment->rooms ?? null,
                    // 'address'        => $apartment->area->city->name . '، ' . $apartment->area->name,
                    'address' => __('cities.' . ($apartment->area->city->name ?? ''))
                        . '، ' .
                        __('areas.' . ($apartment->area->name ?? '')),


                    // 'rental_type'    => $apartment->rent_type,
                    'rental_type' => __('api.rent_' . $apartment->rent_type),

                    // 'apartment_type' => $apartment->type,
                    'apartment_type' => __('api.type_' . $apartment->type),
                    'average_rating'         => round($averageRating, 1),
                    'isFavorite' => FavoriteController::isFavorite($apartment->id),

                ],
                'owner' => [
                    'id'            => $apartment->owner->id,
                    'profile_image' => $apartment->owner->profile->profile_photo ?? null,
                    'full_name'     => $apartment->owner->profile->first_name . ' ' . $apartment->owner->profile->last_name,
                    'phone_number'  => $apartment->owner->phone_number,
                    'bio'           => null, // مؤقتاً
                ]
            ];
        });

        return response()->json([
            // 'message' => 'Approved apartments retrieved successfully.',
            'message' => __('api.apartments_retrieved'),
            'data'    => $formatted
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








    public static function format(Apartment $apartment)
    {
        $user = FacadesAuth::user();

        return [
            'id' => $apartment->id,
            // 'type' => ucfirst($apartment->type),
            'type' => __('api.type_' . $apartment->type),

            'title' => $apartment->title,
            'discription' => $apartment->discription,
            'rent_price' => $apartment->price,
            // 'rent_type' => $apartment->rent_type,
            'rent_type' => __('api.rent_' . $apartment->rent_type),

            'images' => $apartment->apartment_image->map(
                fn($img) =>
                asset('storage/' . $img->image_path)
            )->toArray(),

            'address' => [
                // 'city_name' => $apartment->area->city->name ?? null,
                // 'area_name' => $apartment->area->name ?? null,
                'city_name' => __('cities.' . ($apartment->area->city->name ?? '')),
                'area_name' => __('areas.' . ($apartment->area->name ?? '')),
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
                'id' => $apartment->owner->id,
                'full_name' => ($apartment->owner->profile->first_name ?? '') . ' ' .
                    ($apartment->owner->profile->last_name ?? ''),
                'phone_number' => $apartment->owner->phone_number ?? null,
                'profile_image' => $apartment->owner->profile->profile_photo ?? null,
            ],

            'reviews' => ReviewController::formatForApartment($apartment->id),
            'isFavorite' => FavoriteController::isFavorite($apartment->id),
            'isOwner' => $user ? $apartment->owner_id === $user->id : false,
        ];
    }
}
