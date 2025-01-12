<?php

namespace App\Jobs;

use App\Enums\SendNotificationOptions;
use App\Mail\EventMail;
use App\Models\NotificationLog;
use Illuminate\Bus\Queueable;
use App\Services\NotificationLogService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendCustomerCreateEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $event;
    public $customers;
    public $isUpdated;
    protected $notificationLogService;


    /**
     * Create a new job instance.
     *
     * @param mixed $event The event instance.
     * @param array $customers The list of customer emails.
     * @param bool $isUpdated Whether the event was updated.
     * @param NotificationLogService $notificationLogService The notification log service instance.
     */

    public function __construct($event, $customers, $isUpdated = false, NotificationLogService $notificationLogService)
    {
        $this->event = $event;
        $this->customers = $customers;
        $this->isUpdated = $isUpdated;
        $this->notificationLogService = $notificationLogService;
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
            if ($customer->notificationSettings) {
                $notificationSettings = $customer->notificationSettings;
                $send_notification_options = $notificationSettings->send_notification_options;
                if (in_array(SendNotificationOptions::Events->value, $send_notification_options)) {
                    if ($notificationSettings->method_send_notification == "telegram") {
                        try {
                            $chatId = $notificationSettings->telegram_chat_id;
                            $telegramMessage = "";
                            $telegramMessage .= $this->isUpdated ? 'Event Updated!' : 'New Event Created!' . "\n";
                            $telegramMessage .= $this->event->event_name . "\n";
                            $telegramMessage .= $this->isUpdated ? 'We’ve made updates to an upcoming event. Here are the updated details:' : 'We’re excited to announce a new event! Here are the details:' . "\n";
                            $telegramMessage .= " Event Information: \n";
                            $telegramMessage .= "Name: " . $this->event->event_name . "\n";
                            $telegramMessage .= "Details: " . $this->event->details . "\n";
                            $telegramMessage .= "Start Date: " . Carbon::parse($this->event->start_date)->format('l, F j, Y') . "\n";
                            $telegramMessage .= "End Date: " . Carbon::parse($this->event->end_date)->format('l, F j, Y') . "\n\n";
                            $telegramMessage .= "Thank you for staying connected with us!"  . "\n";
                            $telegramMessage .= now()->year . " All Rights Reserved"  . "\n";

                            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                                'chat_id' => $chatId,
                                'text' => $telegramMessage,
                            ]);
                            $telegramNotificationLog = "";
                            $telegramNotificationLog = $this->notificationLogService->createNotificationLog(
                                user_id: $customer->id,
                                notification_method: 'telegram',
                                reason_notification_send: 'Event Creation',
                                description: 'Event creation telegram notification  for event ID ' . $this->event->id . ' sent to ' . $customer->id
                            );
                        } catch (Exception $e) {
                            // Log the telegram failure
                            if ($telegramNotificationLog != null)
                                $this->notificationLogService->updateNotificationLog(
                                    $telegramNotificationLog,
                                    'Event ID ' . $this->event->id . ' telegram notification failed to send to ' . $customer->id
                                );
                            // Log the exception
                            Log::error('Failed to send email.', [
                                'event_id' => $this->event->id,
                                'customer_id' => $customer->id,
                                'customer_chat_id' =>   $chatId,
                                'error_message' => $e->getMessage(),
                                'error_trace' => $e->getTraceAsString(),
                            ]);
                        }
                    } else {
                        try {
                            // Send the event email to the customer
                            Mail::to($customer->email)->send(new EventMail($this->event, $this->isUpdated));
                            $mailNotificationLog = "";
                            // Log the sent email
                            $mailNotificationLog = $this->notificationLogService->createNotificationLog(
                                user_id: $customer->id,
                                notification_method: 'mail',
                                reason_notification_send: 'Event Creation',
                                description: 'Event creation email for event ID ' . $this->event->id . ' sent to ' . $customer->id
                            );
                        } catch (Exception $e) {
                            // Log the email failure
                            if ($mailNotificationLog != null)
                                $this->notificationLogService->updateNotificationLog(
                                    $mailNotificationLog,
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
                } else {
                    continue;
                }
            } else {
                continue;
            }
        }
    }
}
