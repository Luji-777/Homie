<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function send(User $user, string $title,string $body , string $type)
    {
        // 1. خزن الإشعار في الداتا بيز أولاً
        $notification = Notification::create([
            'user_id'    => $user->id,
            'title'   => $title,
            'body'    => $body,
            'type'       => $type,
        ]);

        // 2. إرسال push عبر FCM إذا كان عنده token
        if ($user->fcm_token) {
            try {
                $messaging = Firebase::messaging();

                $message = CloudMessage::new()
                    ->withNotification([
                        'title' => $title,
                        'body'  => $body,
                    ])
                    ->withData([
                        'type'       => $type,
                        'notification_id' => (string) $notification->id,
                    ]);

                $messaging->sendMulticast($message, [$user->fcm_token]);
            } catch (\Exception $e) {
                Log::error('FCM Send Failed: ' . $e->getMessage());
                // لو الـ token منتهي، ممكن نحذفه
                // $user->update(['fcm_token' => null]);
            }
        }

        return $notification;
    }
}
