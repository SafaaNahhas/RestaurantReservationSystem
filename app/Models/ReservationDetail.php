<?php

namespace App\Models;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class ReservationDetail
 *
 * Represents the details of a reservation, including status, cancellation reasons, and rejection reasons.
 *
 * @package App\Models
 *
 * */
class ReservationDetail extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reservation_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reservation_id',
        'status',
        'cancelled_at',
        'cancellation_reason',
        'rejection_reason',
    ];

    /**
     * Get the reservation this detail belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class); // Inverse one-to-one with Reservation
    }
}
