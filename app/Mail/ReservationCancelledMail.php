<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationCancelledMail extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * The reservation instance containing reservation details.
     *
     * @var mixed
     */
    public $reservation;

     /**
     * Create a new message instance.
     *
     * @param mixed $reservation The reservation details to be included in the email.
     */
    public function __construct($reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Build the message.
     *
     * This method configures the email subject, view, and passes the reservation data
     * to the view template for rendering.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your Reservation Has Been Cancelled')
                    ->view('emails.reservation_cancelled')
                    ->with(['reservation' => $this->reservation]);
    }

}
