<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'city_id'];

    // Define relationship to City model
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    // Define relationship to User model
    public function profiles()
    {
        return $this->hasMany(User::class);
    }
}