<?php

namespace App\Models;

use App\Models\User;
use App\Models\Event;
use App\Models\Table;
use App\Models\ReservationLog;
use App\Models\ReservationDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Mass-assignable attributes
    protected $fillable = ['user_id', 'manager_id', 'table_id', 'start_date', 'end_date', 'guest_count', 'services', 'status','cancelled_at '];
    /**
     * Relationship: A reservation belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Relationship: A reservation belongs to a manager.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    /**
     * Relationship: A reservation belongs to a table.
     */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }
    /**
     * Relationship: A reservation has many logs.
     */
    public function reservationLogs()
    {
        return $this->hasMany(ReservationLog::class);
    }
    /**
     * Relationship: A reservation has many events.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }
    /**
     * Get the rating associated with the reservation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function rating()
    {
        return $this->hasOne(Rating::class);// One-to-one relationship with Rating
    }

    /**
     * Get the details of the reservation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function details()
    {
        return $this->hasOne(ReservationDetail::class);// One-to-one relationship with ReservationDetail
    }



    protected static function booted()
    {
        // Hook into the "creating" event of the model
        static::creating(function ($reservation) {
            // Retrieve the first active emergency (if any)
            $emergency = Emergency::where('is_active', true)->first();

            // If an active emergency exists, perform further checks
            if (!empty($emergency)) {
                // Check for emergencies that conflict with the reservation
                $conflictingEmergencies = Emergency::where(function ($query) use ($reservation) {
                    $query->where(function ($subQuery) use ($reservation) {
                        // Condition 1: Emergency with a definite start and end date
                        // overlaps with the reservation period
                        $subQuery->where('start_date', '<=', $reservation->start_date)
                            ->where('end_date', '>=', $reservation->end_date);
                    })
                        ->orWhere(function ($subQuery) use ($reservation) {
                            // Condition 2: Ongoing emergency (no end date)
                            // that starts before or on the reservation's start date
                            $subQuery->where('start_date', '<=', $reservation->start_date)
                                ->whereNull('end_date');
                        });
                })
                    ->exists(); // Check if any emergencies match the conditions

                // If there are conflicting emergencies, prevent the reservation from being created
                if ($conflictingEmergencies) {
                    throw new \Exception('The reservation cannot be made because the restaurant is closed due to an ongoing emergency.');
                }
            }
        });
    }

}
