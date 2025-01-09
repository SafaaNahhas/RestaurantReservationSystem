<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventMail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
public $isUpdated;


    /**
     * Create a new message instance.
     *
     * @param $event
     * @param $isUpdated
     */
    public function __construct($event, $isUpdated)
    {
        $this->event = $event;
        $this->isUpdated = $isUpdated;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->isUpdated
            ? 'Event Updated: ' . $this->event->name
            : 'New Event Created ' . $this->event->name;

        return $this->subject($subject)
                    ->view('emails.event_emails')
                    ->with([
                        'event' => $this->event,
                        'isUpdated' => $this->isUpdated,
                    ]);
    }

}
