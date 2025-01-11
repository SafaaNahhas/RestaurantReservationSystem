<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmergencyClosureMail extends Mailable
{
    use Queueable, SerializesModels;


    public $reservation;
    public $restaurant;
    public function __construct(Restaurant $restaurant, Reservation $reservation)
    {

        $this->reservation = $reservation;
        $this->restaurant = $restaurant;
    }

    public function build()
    {
        return $this->subject('Apology for cancellation of reservation')
            ->view('emails.emergency_closure')
            ->with([
                'restaurantName' => $this->restaurant->name,
                'reservationDate' => Carbon::parse($this->reservation->start_date)->format('Y-m-d'),
                'customer_name'   => $this->reservation->user->name
            ]);
    }
}
