<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Mass-assignable attributes
    protected $fillable = ['event_name', 'start_date', 'end_date', 'details', 'reservation_id'];
    /**
     * Relationship: An event belongs to a reservation.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
