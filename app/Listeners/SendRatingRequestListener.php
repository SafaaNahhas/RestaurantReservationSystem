<?php

namespace App\Listeners;

use App\Services\NotificationLogService;
use App\Jobs\SendRatingRequestJob;
use App\Events\ReservationCompleted;

class SendRatingRequestListener
{
    /**
     * Handle the event.
     */
    public function handle(ReservationCompleted $event)
    {

        $notificationLogService = new NotificationLogService();

        SendRatingRequestJob::dispatch($event->reservation, $notificationLogService);
    }
}