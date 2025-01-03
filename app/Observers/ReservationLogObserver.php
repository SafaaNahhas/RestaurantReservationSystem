<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Models\ReservationLog;
use Illuminate\Support\Facades\Auth;

class ReservationLogObserver
{
     /**
     * Log when a reservation is created.
     *
     * @param Reservation $reservation
     * @return void
     */
    public function created(Reservation $reservation): void
    {
        $this->createReservationLog($reservation, $reservation->status);

    }

    /**
     * Log when a reservation is updated (status change only).
     *
     * @param Reservation $reservation
     * @return void
     */
    public function updated(Reservation $reservation): void
    {
        if ($reservation->isDirty('status')) {
            $this->createReservationLog($reservation, $reservation->status);
        }
    }

    /**
     * Log when a reservation is deleted.
     *
     * @param Reservation $reservation
     * @return void
     */
    public function deleted(Reservation $reservation): void
    {
        $this->createReservationLog($reservation, 'deleted');

    }

    /**
    * Log when a reservation is restored.
     *
     * @param Reservation $reservation
     * @return void
     */
    public function restored(Reservation $reservation): void
    {
        $this->createReservationLog($reservation, 'restored');

    }

    /**
     * Create a log entry for reservation events.
     *
     * @param Reservation $reservation
     * @param string $status
     * @return void
     */
    public function createReservationLog(Reservation $reservation, $status)
    {
        $logNumber = ReservationLog::where('reservation_id', $reservation->id)->max('log_number') + 1;
        // Skip if reservation does not exist
        if (!$reservation->exists) {
            return;
        }

        ReservationLog::create([
            'reservation_id' => $reservation->id,
            'status' => $status,
            'log_time' => now(),
            'log_number' => $logNumber,
            'changed_by' => Auth::id(),
        ]);
    }
}
