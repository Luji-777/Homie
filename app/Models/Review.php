<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $guarded = [];

    // Define relationship to Apartment model
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    // Define relationship to Booking model
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
