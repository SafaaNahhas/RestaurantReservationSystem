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
    public $isUpdated; 
    
        /**
         * Create a new job instance.
         *
         * @param $event
         * @param $customerEmails
         */
    public function __construct($event, $customerEmails, $isUpdated = false)
    {
        $this->event = $event;
        $this->customerEmails = $customerEmails;
        $this->isUpdated = $isUpdated;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->customerEmails as $email) {
            Mail::to($email)->send(new EventMail($this->event, $this->isUpdated));
        }
    }
    
    
    
}
