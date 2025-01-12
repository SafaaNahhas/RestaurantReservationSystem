<?php

namespace App\Jobs\Emergency;

use Carbon\Carbon;
use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use App\Services\NotificationLogService;
use App\Mail\EmergencyClosureMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class SendEmergencyClosureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $affectedReservations;
    protected NotificationLogService $notificationLogService;

   /**
     * Create a new job instance.
     */
    public function __construct($affectedReservations, NotificationLogService $notificationLogService)
    {
        $this->notificationLogService = $notificationLogService;
        $this->affectedReservations = $affectedReservations;
    }

   /**
     * Execute the job.
     */
    public function handle()
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');

        // Get the first restaurant record from the database
        $restaurant = Restaurant::first();

        // Iterate over the collection of affected reservations
        foreach ($this->affectedReservations as $reservation) {
            // Send an email notification to the user associated with the reservation
            if (!$reservation->user->notificationSettings) {
                $notificationSettings = $reservation->user->notificationSettings;
                if ($notificationSettings->method_send_notification == "telegram") {
                    try {
                        $chatId = $notificationSettings->telegram_chat_id;
                        $telegramMessage = "";
                        $telegramMessage .=  "Dear " . $reservation->user->name . "\n";
                        $telegramMessage .=  "We regret to inform you that due to unforeseen circumstances,
                                 the restaurant " . $restaurant->name . " will be closed on " .  Carbon::parse($reservation->start_date)->format('Y-m-d') . "\n\n";
                        $telegramMessage .= " Thank you for your patience. We look forward to serving you soon!\n";
                        $telegramMessage .= now()->year . ' ' . $restaurant->name . ". All rights reserved ";
                        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                            'chat_id' => $chatId,
                            'text' => $telegramMessage,
                        ]);
                        $telegramNotificationLog = $this->notificationLogService->createNotificationLog(
                            user_id: $reservation->user->id,
                            notification_method: 'telegram',
                            reason_notification_send: 'Emergency Closure Telegram',
                            description: 'Reservation in ' . $reservation->start_date
                        );
                    } catch (\Exception $e) {
                        // Log the telegram failure
                        Log::error('Error sending email to user ' . $reservation->user->id . ': ' . $e->getMessage());
                        $this->notificationLogService->updateNotificationLog(
                            notificationLog: $telegramNotificationLog,
                            description: 'Reservation in ' . $reservation->start_date
                        );
                    }
                } else {
                    try {
                        Mail::to($reservation->user->email)
                            ->send(new EmergencyClosureMail($restaurant, $reservation));
                        // Log the email success
                        $mailNotificationLog = $this->notificationLogService->createNotificationLog(
                            user_id: $reservation->user->id,
                            notification_method: 'mail',
                            reason_notification_send: 'Emergency Closure Mail',
                            description: 'Reservation in ' . $reservation->start_date
                        );
                    } catch (\Exception $e) {
                        // Log the email failure
                        Log::error('Error sending notification to user ' . $reservation->user->id . ': ' . $e->getMessage());
                        $this->notificationLogService->updateNotificationLog(
                            notificationLog: $mailNotificationLog,
                            description: 'Reservation in ' . $reservation->start_date
                        );
                    }
                }
            } else {
                continue;
            }
        }
    }
}
