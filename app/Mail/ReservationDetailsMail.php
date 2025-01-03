<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationDetailsMail extends Mailable
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
     * This method sets up the email subject, attaches the view template,
     * and logs the reservation details for debugging purposes.
     *
     * @return $this
     */
    public function build()
    {   Log::info("Reservation details in Mailable: ", $this->reservation->toArray());
        return $this->subject('Your Reservation Confirm ')
                    ->view('emails.reservation_details')
                    ->with(['reservation' => $this->reservation]);
    }
}
