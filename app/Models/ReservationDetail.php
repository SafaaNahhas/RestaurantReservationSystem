<?php

namespace App\Models;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReservationDetail extends Model
{
    use HasFactory;
    protected $table = 'reservation_details';
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
        return $this->belongsTo(Reservation::class);// Inverse one-to-one with Reservation
    }

}
