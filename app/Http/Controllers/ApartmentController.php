<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApartmentRequest;
use App\Http\Requests\UpdateApartmentRequest;
use App\Http\Requests\ApartmentFilterRequest;
use App\Models\Apartment;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;

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
        $apartment = Apartment::with(['area.city'])->findOrFail($id);

        return response()->json([
            'apartment' => $apartment
        ], 201);
    }



    // Update the specified resource in storage.
    // Update the specified resource in storage.
    public function update(UpdateApartmentRequest $request, int $id)
    {
        $owner_id = FacadesAuth::user()->id;

        // جلب الشقة مع التأكد من وجودها
        $apartment = Apartment::findOrFail($id);

        // التحقق من الملكية
        if ($apartment->owner_id !== $owner_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        // تحديث البيانات الأساسية للشقة (بدون الصور)
        $apartment->update($request->validated());

        // إذا تم إرسال صور جديدة → نحذف القديمة ونرفع الجديدة
        if ($request->hasFile('images')) {
            // حذف كل الصور المرتبطة بالشقة (بما فيها الملفات من التخزين إذا بدك)
            $apartment->apartment_image()->delete();
            // أو إذا استخدمت alias images():
            // $apartment->images()->delete();

            // رفع الصور الجديدة مع تحديد الغلاف
            $coverIndex = $request->input('cover_index', 0);
            $this->uploadImages($apartment, $request->file('images'), (int)$coverIndex);
        }

        // تحميل العلاقات المطلوبة لإرجاع البيانات كاملة ومحدثة
        $apartment->load([
            'area.city',
            'apartment_image',     // أو 'images' إذا أضفت الـ alias
            'isCover'           // تأكد إنك أضفت هالعلاقة في الموديل
        ]);

        return response()->json([
            'message'   => 'Apartment updated successfully.',
            'apartment' => $apartment
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

        // فلتر حسب رينج السعر
        if ($request->filled('price_min')) {
            $query->where('price_per_day', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price_per_day', '<=', $request->price_max);
        }

        // فلتر حسب عدد الغرف
        if ($request->filled('rooms')) {
            $query->where('bedrooms', $request->rooms);
        }

        // فلتر حسب وجود WiFi
        if ($request->filled('wifi')) {
            $Wifi = filter_var($request->wifi, FILTER_VALIDATE_BOOLEAN);
            $query->where('wifi', $Wifi);
        }



        // ترتيب حسب الأحدث أولاً (اختياري)
        $apartments = $query->latest()->paginate(2); // 12 شقة في الصفحة، غيّر الرقم كيف ما بدك
        return response()->json([
            'message' => 'Apartments retrieved successfully.',
            'data'    => $apartments,
            'filters' => $request->only(['city_id', 'area_id', 'price_min', 'price_max', 'rooms', 'wifi'])
        ], 200);
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
