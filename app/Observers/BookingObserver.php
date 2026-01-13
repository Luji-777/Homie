<?php

namespace App\Observers;
 use App\Services\NotificationService;
use App\Models\Booking;

class BookingObserver
{

    public function created(Booking $booking): void
    {

    $owner = $booking->apartment->owner;

    app(NotificationService::class)->send(
        user: $owner,
        title: 'New booking waiting your replay',
        body: "tenant {$booking->tenant->first_name} booking your apartment {$booking->apartment->title} from{$booking->check_in} to {$booking->check_out}"

    );

    }


    public function updated(Booking $booking): void
    {

    //إشعارات رح تنبعت للمستأجر وقت تتغير حالة الحجز
    if ($booking->isDirty('request_status')) {
        $tenant = $booking->tenant;
        $owner = $booking->owner;

        $request_status = match ($booking->request_status) {
            'pending_owner'  => 'Pending Owner Approval',
            'owner_accepted' => 'Booking Approved',
            'owner_rejected' => 'Booking Rejected',

            'tenant_cancel_request' => 'Pending Owner Approval to cancel the booking',
            'owner_cancel_accepted'=> 'Booking Cancelled',
            'owner_cancel_rejected' => 'The cancellation request was rejected',

            'tenant_modify_request' => 'Pending Owner Approval to modify the booking',
            'owner_modify_accepted'=>   'Booking modified',
            'owner_modify_rejected'=>   'The modification request was rejected',

            'completed'      => 'Booking Completed',

            default          => 'Booking status updated',
        };

        app(NotificationService::class)->send(
            user: $tenant,
            title: 'Booking Status Update',
            body: "Apartment: {$booking->apartment->title}\nNew request_status: {$request_status}"

        );

        //اشعارات رح تنبعت للمالك وقت تتغير حالة الحجز
         if ($booking->isDirty('request_status')) {

        // طلب إلغاء
        if ($booking->request_status === 'cancelled') {
            app(NotificationService::class)->send(
                user: $owner,
                title: 'Booking Cancellation Request',
                body: "Tenant {$tenant->first_name} has requested to cancel the booking for apartment {$booking->apartment->request_status}.\nPlease approve or reject the cancellation request.",
            );
        }
    }
 //طلب تعديل
    if ($booking->isDirty(['check_in', 'check_out'])) {
        app(NotificationService::class)->send(
            user: $owner,
            title: 'Booking Modification Request',
            body: "Tenant {$tenant->first_name} has requested changes to the booking for apartment {$booking->apartment->request_status}.\nPlease review and approve or reject the modifications.",
        );
    }
}}


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
