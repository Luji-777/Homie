<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Container\Attributes\Auth;

use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
{
    $notifications = FacadesAuth::user()->notifications()
        ->latest()
        ->paginate(15);

    return response()->json([
        'message' => 'Notifications retrieved',
        'notifications' => $notifications->items(),
        'unread_count' => FacadesAuth::user()->notifications()->where('is_read', false)->count(),
        'pagination' => [
            'current_page' => $notifications->currentPage(),
            'last_page'    => $notifications->lastPage(),
            'total'        => $notifications->total(),
        ]
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
