<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PhoneNumber
 *
 * Represents a phone number associated with a restaurant.
 *
 * @package App\Models
 *
 * */
class PhoneNumber extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['PhoneNumber', 'description', 'restaurant_id'];

    /**
     * Relationship: A phone number belongs to a restaurant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
