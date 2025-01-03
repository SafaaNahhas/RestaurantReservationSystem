<?php

namespace App\Jobs;

use App\Events\EmergencyOccurred;
use App\Mail\EmergencyClosureMail;
use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmergencyClosureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $affectedReservations;

    public function __construct($affectedReservations)
    {

        $this->affectedReservations = $affectedReservations;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Get the first restaurant record from the database
        $restaurant = Restaurant::first();

        // Iterate over the collection of affected reservations
        foreach ($this->affectedReservations as $reservation) {
            // Send an email notification to the user associated with the reservation
            // The EmergencyClosureMail mailable is used to construct the email content
            // It passes the restaurant and reservation details to the email template
            Mail::to($reservation->user->email)
                ->send(new EmergencyClosureMail($restaurant, $reservation));
        }
    }
}
