<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Restaurant
 *
 * Represents a restaurant, including details such as name, location, operating hours, rating, and contact information.
 * It also supports associations with images, phone numbers, and emails.
 *
 * @package App\Models
 *
 * */
class Restaurant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'location',
        'opening_hours',
        'closing_hours',
        'rating',
        'website',
        'description',
    ];

    /**
     * Get all images associated with the restaurant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imagable');
    }


    /**
     * Get all phone numbers associated with the restaurant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function phoneNumbers()
    {
        return $this->hasMany(PhoneNumber::class);
    }


    /**
     * Get all emails associated with the restaurant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function emails()
    {
        return $this->hasMany(Email::class);
    }
}
