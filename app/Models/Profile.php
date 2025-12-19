<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $guarded = [];

    // Define relationship to User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define relationship to City model
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    // Define relationship to Area model
    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
