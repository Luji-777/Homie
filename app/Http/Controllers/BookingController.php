<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Http\Request;
use App\Models\Apartment;
use App\Rules\NoOverlappingBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // لو محتاج استعلامات raw معقدة لاحقاًse Illuminate\Support\Carbon;

class BookingController extends Controller
{
    /**
     * جلب كل الأيام المحجوزة لشقة معينة من اليوم الحالي وحتى شهرين قدام
     */
    public function getBookedDates(int $apartmentId)
    {
        // التاريخ الحالي (بدون وقت)
        $startDate = Carbon::today();

        // بعد شهرين بالضبط (نهاية اليوم)
        $endDate = Carbon::today()->addMonths(2)->endOfDay();

        // جلب الحجوزات اللي تتداخل مع الفترة (فقط المقبولة أو المعلقة)
        $bookings = Booking::where('apartment_id', $apartmentId)
            ->whereIn('status', ['pending', 'confirmed', 'approved']) // عدل الحالات حسب نظامك
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('check_in', [$startDate, $endDate])
                    ->orWhereBetween('check_out', [$startDate, $endDate])
                    ->orWhereRaw('? BETWEEN check_in AND check_out', [$startDate])
                    ->orWhereRaw('? BETWEEN check_in AND check_out', [$endDate]);
            })
            ->select('check_in', 'check_out')
            ->get();

        $bookedDates = [];

        foreach ($bookings as $booking) {
            $checkIn = Carbon::parse($booking->check_in);
            $checkOut = Carbon::parse($booking->check_out)->subDay(); // اليوم قبل الخروج (لأن check_out الشقة تتحرر فيه)

            $current = $checkIn->copy();

            while ($current->lte($checkOut)) {
                if ($current->between($startDate, $endDate)) {
                    $bookedDates[] = $current->toDateString(); // '2025-12-28'
                }
                $current->addDay();
            }
        }

        // إزالة التكرار وترتيب الأيام
        $bookedDates = array_unique($bookedDates);
        sort($bookedDates);

        return response()->json([
            'apartment_id' => $apartmentId,
            'from'         => $startDate->toDateString(),
            'to'           => $endDate->toDateString(),
            'booked_dates' => array_values($bookedDates), // إعادة ترقيم المصفوفة
        ]);
    }






    public function index()
    {
        // $booking=Booking::forUser(auth()->user())->latest()->get();
        //return response()->json($booking);
    }






    public function store(Request $request, int $id)
    {
        // التحقق من البيانات + قاعدة عدم التداخل
        $validated = $request->validate([
            'check_in'  => 'required|date|after_or_equal:today',
            'check_out' => [
                'required',
                'date',
                'after:check_in',
                new NoOverlappingBooking($id), // افتراضاً إن القاعدة دي بتستثني الحجوزات المرفوضة أو الملغاة
            ],
        ]);

        // جلب الشقة مع التأكد من وجودها
        $apartment = Apartment::findOrFail($id);

        // حساب عدد الأيام
        $checkIn = Carbon::createFromFormat('d-m-Y', $request->check_in)->format('Y-m-d'); // شكل التاريخ
        $checkIn = Carbon::createFromFormat('d-m-Y', $request->check_out)->format('Y-m-d'); // شكل التاريخ

        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);
        $totalDays = $checkIn->diffInDays($checkOut);

        // حساب السعر (يومي أو شهري)
        if ($totalDays >= 30) {
            $fullMonths = floor($totalDays / 30);
            $remainingDays = $totalDays % 30;
            $totalPrice = ($fullMonths * $apartment->price_per_month) + ($remainingDays * $apartment->price_per_day);
        } else {
            $totalPrice = $totalDays * $apartment->price_per_day;
        }

        // إنشاء الحجز بحالة pending و owner_approval = false
        $booking = Booking::create([
            'apartment_id'   => $apartment->id,
            'owner_id'       => $apartment->owner_id,       // مهم جداً نحفظه هنا
            'tenant_id'      => FacadesAuth::user()->id,
            'check_in'       => $validated['check_in'],
            'check_out'      => $validated['check_out'],
            'total_price'    => $totalPrice,
            'status'         => 'pending',                  // صراحةً
            'owner_approval' => false,                      // صراحةً
            'cancellation_reason' => null,
        ]);

        return response()->json([
            'message' => 'تم تقديم طلب الحجز بنجاح، بانتظار موافقة صاحب الشقة',
            'booking' => $booking,
            'total_price' => $totalPrice,
        ], 201);
    }








    /**
     * دالة لمعالجة رد صاحب الشقة (موافقة أو رفض) على طلب الحجز
     */
    public function handleOwnerResponse(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        // التأكد إن اللي بيعمل الطلب هو صاحب الشقة فعلاً
        if ($booking->owner_id !== FacadesAuth::user()->id) {
            return response()->json([
                'message' => 'غير مصرح لك بإدارة هذا الحجز'
            ], 403);
        }

        // التأكد إن الحجز لا يزال معلق (pending)
        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'لا يمكن تغيير حالة حجز غير معلق'
            ], 400);
        }

        // التحقق من الإجراء المطلوب
        $request->validate([
            'action' => 'required|in:approve,reject'
        ]);

        if ($request->action === 'approve') {
            $booking->update([
                'status' => 'owner_approved',
                'owner_approval' => true,
            ]);

            return response()->json([
                'message' => 'تمت الموافقة على الحجز بنجاح',
                'booking' => $booking->fresh()
            ], 200);
        }

        if ($request->action === 'reject') {
            $booking->update([
                'status' => 'owner_rejected',
                'owner_approval' => false,
            ]);

            return response()->json([
                'message' => 'تم رفض الحجز',
                'booking' => $booking->fresh()
            ], 200);
        }
    }



    /**
     * طلب إلغاء الحجز من المستأجر (بانتظار موافقة المالك)
     */
    public function requestCancellation(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        // التأكد إن المستخدم هو المستأجر
        if ($booking->tenant_id !== FacadesAuth::user()->id) {
            return response()->json([
                'message' => 'غير مصرح لك بهذا الإجراء'
            ], 403);
        }

        // التأكد إن الحجز مؤكد (مش pending أو مرفوض)
        if (!in_array($booking->status, ['owner_approved', 'paid'])) {
            return response()->json([
                'message' => 'لا يمكن إلغاء حجز غير مؤكد'
            ], 400);
        }

        // التحقق من السبب
        $request->validate([
            'reason' => 'required|string|min:10|max:1000'
        ]);

        // حفظ السبب + تغيير الحالة إلى حالة وسيطة (هنستخدم 'pending' مؤقتًا؟ لا)
        // بدل ذلك: نحفظ السبب فقط، ونخلي status كما هو، والمالك يشوف السبب ويغير status إلى cancelled
        $booking->update([
            'cancellation_reason' => $request->reason,
            // status يبقى owner_approved، بس المالك هيشوف إن في سبب إلغاء
        ]);

        // هنا ممكن ترسل notification أو إيميل للمالك (لاحقًا)

        return response()->json([
            'message' => 'تم إرسال طلب الإلغاء بنجاح، بانتظار موافقة صاحب الشقة',
            'booking' => $booking->fresh()
        ], 200);
    }


    /**
     * موافقة المالك على إلغاء الحجز
     */
    public function approveCancellation($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        // التأكد إن المستخدم هو المالك
        if ($booking->owner_id !== FacadesAuth::user()->id) {
            return response()->json([
                'message' => 'غير مصرح لك'
            ], 403);
        }

        // التأكد إن في طلب إلغاء (سبب موجود)
        if (empty($booking->cancellation_reason)) {
            return response()->json([
                'message' => 'لا يوجد طلب إلغاء'
            ], 400);
        }

        // إلغاء الحجز فعليًا
        $booking->update([
            'status' => 'cancelled',
            'owner_approval' => false, // اختياري
        ]);

        return response()->json([
            'message' => 'تم إلغاء الحجز بنجاح',
            'booking' => $booking->fresh()
        ], 200);
    }





    
    /**
     * طلب تعديل تواريخ الحجز من المستأجر (بانتظار موافقة المالك)
     */
    public function requestModification(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        // التأكد إن المستخدم هو المستأجر
        if ($booking->tenant_id !== FacadesAuth::user()->id) {
            return response()->json([
                'message' => 'غير مصرح لك بهذا الإجراء'
            ], 403);
        }

        // التأكد إن الحجز مؤكد
        if (!in_array($booking->status, ['owner_approved', 'paid'])) {
            return response()->json([
                'message' => 'لا يمكن تعديل حجز غير مؤكد'
            ], 400);
        }

        // التحقق من البيانات الجديدة
        $validated = $request->validate([
            'new_check_in'  => 'required|date|after_or_equal:today',
            'new_check_out' => 'required|date|after:new_check_in',
            'reason'        => 'required|string|min:10|max:1000',
        ]);

        // تحقق من عدم التداخل مع حجوزات أخرى مؤكدة (نستخدم القاعدة نفسها، مع استثناء الحجز الحالي)
        $overlapRule = new NoOverlappingBooking($booking->apartment_id, $bookingId);
        $validator = validator($validated, [
            'new_check_out' => [$overlapRule]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'التواريخ الجديدة متداخلة مع حجز آخر مؤكد'
            ], 422);
        }

        // حفظ الطلب في cancellation_reason كنص واضح
        $modificationText = "طلب تعديل الحجز:\n" .
            "تاريخ الدخول الجديد: {$validated['new_check_in']}\n" .
            "تاريخ الخروج الجديد: {$validated['new_check_out']}\n" .
            "السبب: {$validated['reason']}";

        $booking->update([
            'cancellation_reason' => $modificationText,
            // status يبقى كما هو، بس المالك هيعرف إنه طلب تعديل
        ]);

        return response()->json([
            'message' => 'تم إرسال طلب تعديل الحجز بنجاح، بانتظار موافقة صاحب الشقة',
            'booking' => $booking->fresh()
        ], 200);
    }

    /**
     * موافقة المالك على طلب التعديل وتطبيقه
     */
    public function approveModification(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        // التأكد إن المستخدم هو المالك
        if ($booking->owner_id !== FacadesAuth::user()->id) {
            return response()->json([
                'message' => 'غير مصرح لك'
            ], 403);
        }

        // التأكد إن في طلب تعديل (النص يحتوي على "طلب تعديل")
        if (is_null($booking->cancellation_reason) || !str_contains($booking->cancellation_reason, 'طلب تعديل')) {
            return response()->json([
                'message' => 'لا يوجد طلب تعديل'
            ], 400);
        }

        // استخراج التواريخ من النص (بطريقة بسيطة)
        preg_match('/تاريخ الدخول الجديد: (\d{4}-\d{2}-\d{2})/', $booking->cancellation_reason, $inMatches);
        preg_match('/تاريخ الخروج الجديد: (\d{4}-\d{2}-\d{2})/', $booking->cancellation_reason, $outMatches);

        if (empty($inMatches[1]) || empty($outMatches[1])) {
            return response()->json([
                'message' => 'تعذر قراءة التواريخ الجديدة'
            ], 400);
        }

        $newCheckIn = $inMatches[1];
        $newCheckOut = $outMatches[1];

        // حساب السعر الجديد
        $checkIn = Carbon::parse($newCheckIn);
        $checkOut = Carbon::parse($newCheckOut);
        $totalDays = $checkIn->diffInDays($checkOut);

        $apartment = $booking->apartment;

        if ($totalDays >= 30) {
            $fullMonths = floor($totalDays / 30);
            $remainingDays = $totalDays % 30;
            $newPrice = ($fullMonths * $apartment->price_per_month) + ($remainingDays * $apartment->price_per_day);
        } else {
            $newPrice = $totalDays * $apartment->price_per_day;
        }

        // تطبيق التعديل
        $booking->update([
            'check_in' => $newCheckIn,
            'check_out' => $newCheckOut,
            'total_price' => $newPrice,
            'cancellation_reason' => null, // مسح الطلب بعد التنفيذ
        ]);

        return response()->json([
            'message' => 'تم تعديل الحجز بنجاح',
            'booking' => $booking->fresh()
        ], 200);
    }





    

    // هي من عند لجين
    public function myBookings(Request $request)
    {
        $tenant_id = FacadesAuth::user()->id;
        $bookings = Booking::where('tenant_id', $tenant_id)->get();
        // إرجاع بيانات الحجوزات مع الحالة

        return response()->json([
            'bookings' => $bookings
        ], 200);
    }
}
