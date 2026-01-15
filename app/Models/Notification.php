<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // المرسل
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id'); // user_id هو المرسل
    }

    // المستلم
    public function receiver()
    {
        return $this->belongsTo(User::class, 'reciver_id'); // reciver_id هو المستلم
    }


    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
