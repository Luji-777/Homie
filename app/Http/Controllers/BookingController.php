<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreApartmentRequest; // ملاحظة: الاسم هنا غريب شوية، ربما تقصد StoreBookingRequest؟
use App\Models\Apartment;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth as FacadesAuth;
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
    public function show(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id)
    {
        //
    }

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
