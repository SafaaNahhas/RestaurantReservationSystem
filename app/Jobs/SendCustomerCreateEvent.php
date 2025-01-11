<?php

namespace App\Jobs;

use App\Mail\EventMail;
use Illuminate\Bus\Queueable;
use App\Services\EmailLogService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendCustomerCreateEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $event;

    // public $customerEmails;
    // public $isUpdated;

    //     /**
    //      * Create a new job instance.
    //      *
    //      * @param $event
    //      * @param $customerEmails
    //      */
    // public function __construct($event, $customerEmails, $isUpdated = false)
    // {
    //     $this->event = $event;
    //     $this->customerEmails = $customerEmails;
    //     $this->isUpdated = $isUpdated;
    public $customers;
    protected $emailLogService;
    protected $isUpdated;

    /**
     * Create a new job instance.
     *
     * @param mixed $event The event instance.
     * @param array $customers The list of customer emails.
     * @param EmailLogService $emailLogService The email log service instance.
     */
    public function __construct($event, $customers, EmailLogService $emailLogService, $isUpdated = false)
    {
        $this->event = $event;
        $this->customers = $customers;
        $this->emailLogService = $emailLogService;
        $this->isUpdated = $isUpdated;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // foreach ($this->customerEmails as $email) {
        //     Mail::to($email)->send(new EventMail($this->event, $this->isUpdated));

        foreach ($this->customers as $customer) {
            try {
                // Send the event email to the customer
                Mail::to($customer->email)->send(new EventMail($this->event, $this->isUpdated));

                // Log the sent email
                $emailLog=  $this->emailLogService->createEmailLog(
                    $customer->id,
                    'Event Creation',
                    'Event creation email for event ID ' . $this->event->id . ' sent to ' . $customer->id
                );
            } catch (\Exception $e) {
                // Log the email failure
                $this->emailLogService->updateEmailLog(
                    $emailLog,
                    'Event ID ' . $this->event->id . ' email failed to send to ' . $customer->id
                );

                // Log the exception
                Log::error('Failed to send email.', [
                    'event_id' => $this->event->id,
                    'customer_id' => $customer->id,
                    'customer_email' => $customer->email,
                    'error_message' => $e->getMessage(),
                    'error_trace' => $e->getTraceAsString(),
                ]);
            }

        }
    }
}
