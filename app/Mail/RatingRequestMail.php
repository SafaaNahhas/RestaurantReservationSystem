<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;

class RatingRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $createLink;

    /**
     * Create a new message instance.
     */
    public function __construct($createLink)
    {
        $this->createLink = $createLink;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        Log::info('Building the RatingRequestMail.', [
            'create_link' => $this->createLink,
        ]);

        return $this->subject('Rate Your Reservation')
            ->view('emails.rating_request')
            ->with([
                'createLink' => $this->createLink,
            ]);
    }
}
