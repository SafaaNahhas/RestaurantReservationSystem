<?php

namespace App\Listeners;

use App\Services\EmailLogService;
use App\Jobs\SendRatingRequestJob;
use App\Events\ReservationCompleted;

class SendRatingRequestListener
{
    /**
     * Handle the event.
     */
    public function handle(ReservationCompleted $event)
    {

        $emailLogService = new EmailLogService();

        SendRatingRequestJob::dispatch($event->reservation, $emailLogService);

    }
}