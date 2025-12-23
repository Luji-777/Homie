<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Apartment_image extends Model
{
    protected $guarded = [];

    // Define relationship to Apartment model
    public function apartment():BelongsTo
    {
        return $this->belongsTo(Apartment::class,);
    }
}
