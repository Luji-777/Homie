<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    protected $guarded = [];
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function booking()
    {
        return $this->hasMany(Booking::class);
    }

    public function review()
    {
        return $this->hasMany(Review::class);
    }
    public function apartment_image()
    {
        return $this->hasMany(Apartment_image::class);
    }

    public function isCover()
    {
        return $this->hasOne(Apartment_image::class);
    }
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    //-------------------------------------------
    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites', 'apartment_id', 'user_id')->withTimestamps();
    }
    public function message()
    {
        return $this->hasMany(Message::class);
    }
}
