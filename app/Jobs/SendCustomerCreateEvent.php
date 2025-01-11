<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Mail\EventMail;
use Illuminate\Bus\Queueable;
use App\Services\EmailLogService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Enums\SendNotificationOptions;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendCustomerCreateEvent implements ShouldQueue
{ use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $event;
    public $customers;
    public $isUpdated;
    protected $emailLogService;

    /**
     * Create a new job instance.
     *
     * @param mixed $event The event instance.
     * @param array $customers The list of customer emails.
     * @param bool $isUpdated Whether the event was updated.
     * @param EmailLogService $emailLogService The email log service instance.
     */

    public function __construct($event, $customers, $isUpdated = false, EmailLogService $emailLogService)
    {
        $this->event = $event;
        $this->customers = $customers;
        $this->isUpdated = $isUpdated;
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
        $botToken = env('TELEGRAM_BOT_TOKEN');

        foreach ($this->customers as $customer) {
            try {
                $notificationSettings = $customer->notificationSettings;
                $send_notification_options = $notificationSettings->send_notification_options;
                if (in_array(SendNotificationOptions::Events->value, $send_notification_options)) {
                    if ($notificationSettings->method_send_notification == "telegram") {
                        $chatId = $notificationSettings->telegram_chat_id;
                        $telegramMessage = "";
                        $telegramMessage .= $this->isUpdated ? 'Event Updated!' : 'New Event Created!\n';
                        $telegramMessage .= $this->event->event_name . "\n";
                        $telegramMessage .= $this->isUpdated ? 'We’ve made updates to an upcoming event. Here are the updated details:' : 'We’re excited to announce a new event! Here are the details:\n';
                        $telegramMessage .= " Event Information: \n\n";
                        $telegramMessage .= "Name:" . $this->event->event_name . "\n";
                        $telegramMessage .= "Details:" . $this->event->details . "\n";
                        $telegramMessage .= "Start Date:" . Carbon::parse($this->event->start_date)->format('l, F j, Y') . "\n";
                        $telegramMessage .= "End Date:" . Carbon::parse($this->event->end_date)->format('l, F j, Y') . "\n\n\n";
                        $telegramMessage .= "Thank you for staying connected with us!\n";
                        $telegramMessage .= now()->year . " All Rights Reserved.\n";

                        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                            'chat_id' => $chatId,
                            'text' => $telegramMessage,
                        ]);
                    } else {
                        // Send the event email to the customer
                        Mail::to($customer->email)->send(new EventMail($this->event, $this->isUpdated));// Log the sent email
                        $emailLog = $this->emailLogService->createEmailLog(
                            $customer->id,
                            'Event Creation',
                            'Event creation email for event ID ' . $this->event->id . ' sent to ' . $customer->id
                        );
                    }
                } else {
                    continue;
                }
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


