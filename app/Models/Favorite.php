<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $guarded = [];

    // Define relationship to User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define relationship to Apartment model
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
