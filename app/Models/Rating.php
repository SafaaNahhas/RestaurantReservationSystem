<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Rating
 *
 * Represents a rating given by a user for a reservation, including a comment and rating score.
 *
 * @package App\Models
 *
 * */
class Rating extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['user_id', 'reservation_id', 'rating', 'comment'];

    /**
     * Relationship: A rating belongs to a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: A rating belongs to a reservation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Scope: Filter ratings based on rating value.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $rating
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }
}
