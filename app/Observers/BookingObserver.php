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
            reciver: $owner,
            sender: $booking->tenant,
            booking: $booking,
            title: 'New booking waiting your replay',
            body: "Has requested to book your apartment",

        );
    }


    public function updated(Booking $booking): void
    {
        // إشعارات رح تنبعت للمستأجر وقت تتغير حالة الحجز
        if ($booking->isDirty('request_status')) {
            $tenant = $booking->tenant;
            $owner = $booking->owner;
        

            $request_status = match ($booking->request_status) {

                // Tenant Requests (To Owner)
                // 'rent_request'          => 'Has requested to book your apartment',
                'modification_request'  => 'Has requested to modify the booking',
                'cancellation_request'  => 'Has requested to cancel the booking',

                // Owner Responses - Rejections (To Tenant)
                'rent_rejected'         => 'The owner has rejected your booking request',
                'modification_rejected' => 'The owner has rejected your modification request',
                'cancellation_rejected' => 'The owner has rejected your cancellation request',

                // Owner Responses - Approvals (To Tenant)
                'rent_approved'         => 'The owner has approved your booking request',
                'modification_approved' => 'The owner has approved your modification request',
                'cancellation_approved' => 'The owner has approved your cancellation request',


                // 'pending_owner'  => 'Pending Owner Approval',
                // 'owner_accepted' => 'Booking Approved',
                // 'owner_rejected' => 'Booking Rejected',

                // 'owner_cancel_accepted' => 'Booking Cancelled',
                // 'owner_cancel_rejected' => 'The cancellation request was rejected',
                // 'tenant_cancel_request' => 'Pending owner approval to cancel the booking',


                // 'tenant_modify_request' => 'Pending owner approval to modify the booking',
                // 'owner_modify_accepted' =>   'Booking modified',
                // 'owner_modify_rejected' =>   'The modification request was rejected',
                // 'completed'      => 'Booking Completed',
                // default          => 'Booking status updated',
            };

            app(NotificationService::class)->send(
                reciver: $tenant,
                sender: $owner,
                booking: $booking,
                title: 'Booking Status Update',
                body: $request_status
            );

            // طلب إلغاء
            if ($booking->request_status === 'cancellation_request') {
                app(NotificationService::class)->send(
                    reciver: $owner,
                    sender: $tenant,
                    booking: $booking,
                    title: 'Booking Cancellation Request',
                    body: $request_status,
                );
            }



            // طلب تعديل
            if ($booking->request_status === 'modification_request') {
                app(NotificationService::class)->send(
                    reciver: $owner,
                    sender: $tenant,
                    booking: $booking,
                    title: 'Booking Modification Request',
                    body: $request_status,
                );
            }
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
