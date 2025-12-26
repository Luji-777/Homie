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

    // Define relationship to User model (tenant)
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    // Define relationship to Booking model
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
