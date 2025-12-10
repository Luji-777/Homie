<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $guarded = [];
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
