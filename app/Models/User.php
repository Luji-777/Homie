<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// use Symfony\Component\HttpKernel\Profiler\Profile;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use \Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

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
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'user_id', 'id');
    }


    /**
     * The apartments that belong to the user.
     */
    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class, 'owner_id');
    }

    // Define relationship to Booking model
    public function booking(): HasMany
    {
        return $this->hasMany(Booking::class, 'tenant_id');
    }

    // Define relationship to Review model
    public function reviewsAsTenant()
    {
        return $this->hasMany(Review::class, 'tenant_id');
    }

    // Define relationship to Favorite model
    public function favoriteApartments(): BelongsToMany
    {
        return $this->belongsToMany(
            Apartment::class,
            'favorites',        // اسم جدول pivot
            'tenant_id',        // المفتاح اللي بيربط user
            'apartment_id'      // المفتاح اللي بيربط apartment
        )->withTimestamps();
    }



    // Define relationship to Message model
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Define relationship to Message model
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

       public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }
}
