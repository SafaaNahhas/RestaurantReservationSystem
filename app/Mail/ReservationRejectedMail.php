<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * The reservation instance containing reservation Rejected.
     *
     * @var mixed
     */
    public $reservation;

    /**
     * Create a new message instance.
     *
     * @param mixed $reservation The reservation Rejected to be included in the email.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Build the message.
     *
     * This method sets up the email subject, attaches the view template,
     * and logs the reservation Rejected for debugging purposes.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Reservation Rejected')
                    ->view('emails.reservation_reject') // This is the email template
                    ->with([
                        'reservationId' => $this->reservation->id,
                        'rejectionReason' => $this->reservation->details->rejection_reason,
                    ]);
    }
}
