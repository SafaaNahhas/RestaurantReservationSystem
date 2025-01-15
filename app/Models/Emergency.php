<?php

namespace App\Models;

use App\Events\EmergencyOccurred;
use App\Jobs\Emergency\SendEmergencyClosureJob;
use App\Services\NotificationLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Emergency extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'description',
        'is_active'
    ];
    /**
     * Boot the model and handle emergency-related actions on creation.
     *
     * Wraps the logic in a transaction to ensure consistency.
     */
    protected static function booted()
    {
        // Wrap the static boot logic inside a database transaction
        DB::transaction(function () {
            // Hook into the "created" event of the model
            static::created(function ($emergency) {
                // Retrieve all reservations affected by the emergency
                // Affected reservations are those whose start_date falls within the emergency period
                // Exclude reservations that are already cancelled
                $affectedReservations = Reservation::whereBetween(
                    'start_date',
                    [$emergency->start_date, $emergency->end_date]
                )
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->get();

                // Loop through each affected reservation
                foreach ($affectedReservations as $reservation) {
                    // Update the reservation status to "cancelled"
                    $reservation->update(['status' => 'cancelled']);
                }
                $notificationLogService = new NotificationLogService();
                // Dispatch a job to notify affected reservations about the emergency closure
                SendEmergencyClosureJob::dispatch($affectedReservations, $notificationLogService);
            });
        });
    }
}
