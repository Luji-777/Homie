<?php

namespace App\Observers;
 use App\Services\NotificationService;
use App\Models\Booking;

class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {

    if ($booking->isDirty('status')) {
        $tenant = $booking->tenant;

        $status = match ($booking->status) {
            'pending'        => 'Pending Owner Approval',
            'owner_approved' => 'Booking Approved',
            'owner_rejected' => 'Booking Rejected',
            'completed'      => 'Booking Completed',
            'cancelled'      => 'Booking Cancelled',
            default          => 'Booking status updated',
        };

        app(NotificationService::class)->send(
            user: $tenant,
            title: 'Booking Status Update',
            body: "Apartment: {$booking->apartment->name}\nNew status: {$status}",
            type: 'booking_status',
            relatedId: $booking->id
        );
    }
}


    /**
     * Handle the Booking "deleted" event.
     */
    public function deleted(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "restored" event.
     */
    public function restored(Booking $booking): void
    {
        //
    }

    /**
     * Handle the Booking "force deleted" event.
     */
    public function forceDeleted(Booking $booking): void
    {
        //
    }
}
