<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Symfony\Component\HttpKernel\Profiler\Profile;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    //-----------------------------
    // Define relationship to Profile model
    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id','id');
    }

    // Define relationship to Apartment model
    public function apartments()
    {
        return $this->hasMany(Apartment::class, 'owner_id');
    }

    // Define relationship to Booking model
    public function booking()
    {
        return $this->hasMany(Booking::class, 'tenant_id');
    }

    // Define relationship to Favorite model
    public function favoriteApartment()
    {
        return $this->belongsToMany(Apartment::class, 'tenant_id', 'favorite', 'apartment_id')->withTimestamps();
    }

    // Define relationship to Message model
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Define relationship to Message model
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}
