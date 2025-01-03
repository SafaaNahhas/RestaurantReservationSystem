<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Models\Restaurant;
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
        return $this->subject('اعتذار عن إلغاء الحجز')
            ->view('emails.emergency_closure')
            ->with([
                'restaurantName' => $this->restaurant->name,
                'reservationDate' => $this->reservation->start_date,
                'customer_name'   => $this->reservation->user->name
            ]);
    }
}
