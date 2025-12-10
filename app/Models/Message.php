<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $guarded = [];
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    //-----------------------------------------
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
