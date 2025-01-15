<?php

namespace App\Models;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class ReservationLog
 *
 * Represents the log entries for reservations, tracking status changes, log times, and the user who made the change.
 *
 * @package App\Models
 *
 * */
class ReservationLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['reservation_id', 'status', 'log_time', 'log_number', 'changed_by'];

    /**
     * Get the reservation this log belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the user who changed the reservation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
