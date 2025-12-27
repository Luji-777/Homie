<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    //
    public function myBookings()
    {
        $user = FacadesAuth::user();

        $bookings = $user->bookings()->with('apartment')->get();

        return response()->json([
            'status' => 'success',
            'data' => $bookings
        ]);
    }

    public function bookingDetails(Request $request, $id)
    {
        $user = FacadesAuth::user();

        $booking = $user->bookings()->with('apartment')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $booking
        ]);
    }

    public function cancelBooking(Request $request, $id)
    {
        $user = FacadesAuth::user();

        $booking = $user->bookings()->findOrFail($id);

        // تحقق إذا كان الحجز قابل للإلغاء (حسب منطقك)
        if ($booking->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'لا يمكن إلغاء هذا الحجز'
            ], 400);
        }
        // موافقة المالك ضرورية للإلغاء
        if ($booking->apartment->owner_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا يمكنك إلغاء حجز لشقة لا تملكها'
            ], 403);
        }

        $booking->status = 'canceled';
        $booking->save();

        return response()->json([
            'status' => 'success',
            'message' => 'تم إلغاء الحجز بنجاح'
        ]);
    }

}
