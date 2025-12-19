<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $guarded = [];

    // Define relationship to Apartment model
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    // Define relationship to User model for sender and receiver
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id')->withDefault([
            'first_name' => null,
            'avatar'     => null,
        ]);
    }
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
