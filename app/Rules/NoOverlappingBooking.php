<?php

namespace App\Rules;

use App\Models\Booking;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class NoOverlappingBooking implements ValidationRule
{
    protected $apartmentId;
    protected $ignoreBookingId;

    public function __construct($apartmentId, $ignoreBookingId = null)
    {
        $this->apartmentId = $apartmentId;
        $this->ignoreBookingId = $ignoreBookingId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $checkIn = request()->input('check_in');
        $checkOut = request()->input('check_out');


        if (!$checkIn || !$checkOut) {
            return;
        }

        $checkIn = Carbon::parse($checkIn);
        $checkOut = Carbon::parse($checkOut);

        // عم ندور عالحجوزات الفعالة بس (pending أو owner_approved)
        $query = Booking::where('apartment_id', $this->apartmentId)
            ->whereIn('status', ['pending', 'owner_approved']);

        // لو عم نعدل حجز موجود فمنستثني الحجز الحالي
        if ($this->ignoreBookingId) {
            $query->where('id', '!=', $this->ignoreBookingId);
        }

        $overlaps = $query->where(function ($query) use ($checkIn, $checkOut) {
            $query->whereBetween('check_in', [$checkIn, $checkOut])
                  ->orWhereBetween('check_out', [$checkIn, $checkOut])
                  ->orWhere(function ($q) use ($checkIn, $checkOut) {
                      $q->where('check_in', '<=', $checkIn)
                        ->where('check_out', '>=', $checkOut);
                  });
        })->exists();

        if ($overlaps) {
            $fail('The apartment have already been booked',null);
        }
    }
}
