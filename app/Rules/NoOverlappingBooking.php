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
    protected $checkIn;

    public function __construct($apartmentId, $checkIn, $ignoreBookingId = null)
    {
        $this->apartmentId = $apartmentId;
        $this->checkIn = Carbon::parse($checkIn);
        $this->ignoreBookingId = $ignoreBookingId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $checkOut = Carbon::parse($value);

        $query = Booking::where('apartment_id', $this->apartmentId)
            ->whereIn('status', ['pending', 'owner_approved']);

        if ($this->ignoreBookingId) {
            $query->where('id', '!=', $this->ignoreBookingId);
        }

        $overlaps = $query->where(function ($q) use ($checkOut) {
            $q->whereBetween('check_in', [$this->checkIn, $checkOut])
                ->orWhereBetween('check_out', [$this->checkIn, $checkOut])
                ->orWhere(function ($q2) use ($checkOut) {
                    $q2->where('check_in', '<=', $this->checkIn)
                        ->where('check_out', '>=', $checkOut);
                });
        })->exists();

        if ($overlaps) {
            $fail('الفترة المختارة متداخلة مع حجز آخر',null);
        }
    }
}
