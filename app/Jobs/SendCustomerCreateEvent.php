<?php

namespace App\Jobs;

use App\Mail\EventMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCustomerCreateEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $event;
    public $customerEmails;

    /**
     * Create a new job instance.
     *
     * @param $event
     * @param $customerEmails
     */
    public function __construct($event, $customerEmails)
    {
        $this->event = $event;
        $this->customerEmails = $customerEmails;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->customerEmails as $email) {
            Mail::to($email)->send(new EventMail($this->event));
        }
    }
}
