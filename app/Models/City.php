<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['name'];  // الحقول اللي ممكن تملأها

    // Define relationship to Area model
    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    // Define relationship to Profile model
    public function profiles()
    {
        return $this->hasMany(User::class);
    }
    
    // Define relationship to Apartment model through Area
    public function apartments()
    {
        return $this->hasManyThrough(Apartment::class, Area::class);
    }
}