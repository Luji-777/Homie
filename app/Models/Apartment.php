<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    protected $guarded = [];

    // Define relationship to User model (owner)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Define relationship to Area model
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    // Define relationship to City model through Area
    public function city()
    {
        return $this->hasOneThrough(City::class, Area::class, 'id', 'id', 'area_id', 'city_id');
    }   

    // Define relationship to Booking model
    public function booking()
    {
        return $this->hasMany(Booking::class);
    }

    // Define relationship to Review model
    public function review()
    {
        return $this->hasMany(Review::class);
    }

    // Define relationship to Apartment_image model
    public function apartment_image()
    {
        return $this->hasMany(Apartment_image::class);
    }

    // Define relationship to Apartment_image model for cover image
    public function isCover()
    {
        return $this->hasOne(Apartment_image::class);
    }

    // Define relationship to Favorite model
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // Define relationship to User model for users who favorited this apartment
    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites', 'apartment_id', 'user_id')->withTimestamps();
    }

    // Define relationship to Message model
    public function message()
    {
        return $this->hasMany(Message::class);
    }
}
