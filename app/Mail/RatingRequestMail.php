<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;

class RatingRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    // public $createLink;
    // public $viewLink;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        // $this->createLink = $createLink;
        // $this->viewLink = $viewLink;
    }

    /**
     * Build the message.
     */
   public function build()
{
    Log::info('Building the RatingRequestMail.');

    return $this->subject('Rate Your Reservation')
        ->view('emails.rating_request');
}


}
