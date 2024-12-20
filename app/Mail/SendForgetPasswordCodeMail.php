<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendForgetPasswordCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    /**
     * Create a new message instance.
     */
    public function __construct($code)
    {
        $this->code = $code;
    }
    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Verification Code')
            ->view('emails.forget_passeod_code')
            ->with([
                'code' => $this->code,
            ]);
    }
}
