<?php

namespace App\Listeners;

use App\Events\ReservationCompleted;
use App\Jobs\SendRatingRequestJob;

class SendRatingRequestListener
{
    /**
     * Handle the event.
     */
    public function handle(ReservationCompleted $event)
    {
        SendRatingRequestJob::dispatch($event->reservation);
    }
}
