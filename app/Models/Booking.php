<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $guarded = [];

    // Define relationship to User model for tenant
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    // Define relationship to Apartment model
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    // Define relationship to Review model
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    //user bookings
    public function forUser($query,User $user){
        return $query->where('tenant_id',$user->id)
                    ->orwhere('owner_id',$user->id);
    }
}
