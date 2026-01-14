<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'apartment_id',
        'owner_id',
        'tenant_id',
        'check_in',
        'check_out',
        'total_price',
        'status',
        'request_status',
        'cancellation_reason',
        'owner_approval',
    ];

    protected $casts = [
        'check_in'        => 'date',
        'check_out'       => 'date',
        'total_price'     => 'float',
        'owner_approval' => 'boolean',
    ];

    // علاقات مهمة
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    // Define relationship to Review model
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // Scopes مفيدة للاستعلامات المتكررة
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOwnerApproved($query)
    {
        return $query->where('status', 'owner_approved');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}
