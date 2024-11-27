<?php

namespace App\Models;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReservationLog extends Model
{
    use HasFactory;

    // Mass-assignable attributes
    protected $fillable = ['reservation_id', 'status', 'log_time', 'log_number'];
    /**
     * Relationship: A log belongs to a reservation.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
