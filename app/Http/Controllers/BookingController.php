<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\StoreApartmentRequest;
use App\Models\Apartment;
use App\Models\Booking;
use Illuminate\Container\Attributes\Auth as AttributesAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use App\Rules\NoOverlappingBooking;
use Carbon\Carbon as CarbonAlias;
use Illuminate\Support\Carbon;
class BookingController extends Controller
{

   public function index(Request $request)
{
    $user = Auth::user();

    $type = $request->input('type'); // completed, cancelled, current

    $query = Booking::where('tenant_id', $user->id)
        ->with('apartment');

    // fillter
    if ($type === 'completed') {
        $query->where('status', 'completed');
    } elseif ($type === 'cancelled') {
        $query->where('status', 'cancelled');
    } elseif ($type === 'current') {
        $query->whereIn('status', ['pending', 'owner_approved', 'owner_rejected']);
    }
    // if !type => return all booking

    $bookings = $query->latest()->paginate(10);

    $formattedBookings = $bookings->map(function ($booking) {
        $statusText = match ($booking->status) {
            'pending'          => 'Pending Owner Approval',
            'owner_approved'   => 'Approved',
            'owner_rejected'   => 'Rejected',
            'completed'        => 'Completed',
            'cancelled'        => 'Cancelled',
            default            => $booking->status,
        };

        return [
            'property_type'   => $booking->apartment->type ?? 'Not specified',
            'title'  =>          $booking->apartment->title ?? 'Not specified',
            'rental_period'   => $booking->check_in . ' to ' . $booking->check_out,
            'main_image'      => $booking->apartment->main_image ?? null, //edit
            'city'            => $booking->apartment->city ?? 'Not specified',
            'address'         => $booking->apartment->address ?? 'Not specified',
            'booking_status'  => $statusText,
        ];
    });

    return response()->json([
        'bookings'      => $formattedBookings,
        'current_page'  => $bookings->currentPage(),
        'last_page'     => $bookings->lastPage(),
        'total'         => $bookings->total(),
        'per_page'      => $bookings->perPage(),
    ]);
}

    public function store(Request $request,Apartment $apartment)
    {
        // التحقق من البيانات
    $validated = $request->validate([
            'check_in'  => 'required|date|after_or_equal:today',
            'check_out' => [
            'required',
            'date',
            'after:check_in',
        new NoOverlappingBooking($apartment->id),],
       // 'payment_info' => 'required|string',
       // 'notes'     => 'nullable|string',
    ]);


    // حساب عدد الأيام
  //  $check_in = Carbon::createFromFormat('d-m-Y', $request->check_in)->format('Y-m-d');
  //  $check_out = Carbon::createFromFormat('d-m-Y', $request->check_out)->format('Y-m-d');
    $checkIn = Carbon::parse($validated['check_in']);
    $checkOut = Carbon::parse($validated['check_out']);

    $totalDays = $checkIn->diffInDays($checkOut);

    // حساب السعر الجديد (يومي أو شهري)
    if ($totalDays >= 30) {
        $fullMonths = floor($totalDays / 30);           // عدد الشهور الكاملة
        $remainingDays = $totalDays % 30;               // الأيام الباقية

        $totalPrice = ($fullMonths * $apartment->price_per_month) +
                      ($remainingDays * $apartment->price_per_day);
    } else {
        $totalPrice = $totalDays * $apartment->price_per_day;
    }

        $tenant_id = FacadesAuth::user()->id;
        $validated['tenant_id'] = $tenant_id ;

    // إنشاء الحجز
    $booking = Booking::create([
        'apartment_id' => $apartment->id,
        'tenant_id'    => $tenant_id ,
        'owner_id'     => $apartment->owner_id,
        'check_in'     => $validated['check_in'],
        'check_out'    => $validated['check_out'],
        'total_price'  => $totalPrice,
        //'payment_info' => $validated['payment_info'],
       // 'notes'        => $validated['notes'] ?? null,
        'status'       => 'pending',
    ]);

    return response()->json([
        'message'    => 'The booking was successfully submitted',
        'booking'    => $booking,
        'total_price'=> $totalPrice,
    ], 201);
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
}
