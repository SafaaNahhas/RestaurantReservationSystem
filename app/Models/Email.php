<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Email
 *
 * Represents an email address associated with a restaurant in the application.
 *
 * @package App\Models
 *
 * */
class Email extends Model
{
    use HasFactory;

    /**
     * Mass-assignable attributes.
     *
     * @var string[]
     */
    protected $fillable = [
        'email',
        'description',
        'restaurant_id'
    ];

    /**
     * Relationship: An email belongs to a restaurant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
