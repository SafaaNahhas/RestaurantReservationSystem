<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rating extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Mass-assignable attributes
    protected $fillable = ['user_id', 'reservation_id', 'rating', 'comment'];
    /**
     * Relationship: A rating belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: A rating belongs to a reservation.
     */

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Scope: Filter ratings based on rating value.
     */
    public function scopeFilterByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }
}
