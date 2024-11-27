<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Rating;
use App\Models\EmailLog;
use App\Models\Reservation;
use App\Models\FoodCategory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
         'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    /**
     * Relationship: A user has many reservations.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
    /**
     * Relationship: A user has many food categories.
     */
    public function foodCategories()
    {
        return $this->hasMany(FoodCategory::class);
    }
    /**
     * Relationship: A user has many email logs.
     */
    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class);
    }
    /**
     * Relationship: A user has many ratings.
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
