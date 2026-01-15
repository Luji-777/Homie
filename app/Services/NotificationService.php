<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function send(Booking $booking, User $reciver,User $sender, string $title,string $body)
    {
        // 1. خزن الإشعار في الداتا بيز أولاً
        $notification = Notification::create([
            'reciver_id'    => $reciver->id, //المستلم
            'sender_id'  => $sender->id, //المرسل
            'booking_id' => $booking->id,
            'title'   => $title,
            'body'    => $body,
            
        ]);

        // 2. إرسال push عبر FCM إذا كان عنده token
        if ($reciver->fcm_token) {
            try {
                $messaging = Firebase::messaging();

                $message = CloudMessage::new()
                    ->withNotification([
                        'title' => $title,
                        'body'  => $body,
                    ])
                    ->withData([
                        'notification_id' => (string) $notification->id,
                    ]);

                $messaging->sendMulticast($message, [$reciver->fcm_token]);
            } catch (\Exception $e) {
                Log::error('FCM Send Failed: ' . $e->getMessage());
                // لو الـ token منتهي، ممكن نحذفه
                // $user->update(['fcm_token' => null]);
            }
        }

        return $notification;
    }
}
