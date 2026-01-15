<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Container\Attributes\Auth;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
{
    $userId = FacadesAuth::id();

    $notifications = \App\Models\Notification::where('reciver_id', $userId)
        ->latest()
        ->paginate(15);

    $data = $notifications->map(function($notification) {
        return [
            'reciver_id' => $notification->reciver_id,
            'full_name' => $notification->sender->profile->first_name . ' ' . $notification->sender->profile->last_name,
            'profile_image' => $notification->sender->profile->profile_photo ? Storage::url($notification->sender->profile->profile_photo) : null,
            'start_date' => $notification->booking->check_in,
            'end_date' => $notification->booking->check_out,
            'title' => $notification->booking->apartment->title,
            'body' => $notification->body,
            'notifications_type' => $notification->booking->request_status,
            'date' => $notification->created_at
        ];
    });

    return response()->json([
        'data' => $data,
        'pagination' => [
            'total' => $notifications->total(),
            'per_page' => $notifications->perPage(),
            'current_page' => $notifications->currentPage(),
            'last_page' => $notifications->lastPage(),
        ],
    ]);


    
}



    public function markAsRead($id)
    {
        $notification = FacadesAuth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllAsRead()
    {
        FacadesAuth::user()->notifications()->where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json(['message' => 'All marked as read']);
    }
}
