<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use App\Mail\RatingRequestMail;
use App\Services\EmailLogService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendRatingRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reservation;
    protected $emailLogService;

    /**
     * Create a new job instance.
     *
     * @param Reservation $reservation The reservation instance.
     * @param EmailLogService $emailLogService The email log service instance.
     */
    public function __construct(Reservation $reservation, EmailLogService $emailLogService)
    {
        $this->reservation = $reservation;
        $this->emailLogService = $emailLogService;
    }


    /**
     * Execute the job.
     *
     * Sends a rating request notification to the user associated with the reservation.
     */
    public function handle()
    {
        $user = $this->reservation->user;
        $notificationSettings = $user->notificationSettings;

        Log::info('Job started for sending rating notification.', [
            'reservation_id' => $this->reservation->id,
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);

        try {
            // Create links for the rating request
            $ratingLink = url("/api/rating?reservation_id={$this->reservation->id}&user_id={$user->id}");
            $message = "⭐ We value your feedback!\n\n";
            $message .= "Please rate your experience: [Click Here]($ratingLink)";

            // Check notification preferences
            $sendNotificationOptions = is_array($notificationSettings->send_notification_options)
                ? $notificationSettings->send_notification_options
                : (json_decode($notificationSettings->send_notification_options, true) ?: []);

            if (in_array('rating', $sendNotificationOptions)) {
                if ($notificationSettings->method_send_notification === 'telegram' && $notificationSettings->telegram_chat_id) {
                    // Send Telegram message
                    $botToken = env('TELEGRAM_BOT_TOKEN');
                    Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $notificationSettings->telegram_chat_id,
                        'text' => $message,
                        'parse_mode' => 'Markdown',
                    ]);

                    Log::info('Rating request sent via Telegram.', [
                        'user_id' => $user->id,
                        'reservation_id' => $this->reservation->id,
                    ]);
                } elseif ($notificationSettings->method_send_notification === 'mail') {
                    // Send rating request email
                    Mail::to($user->email)->send(new RatingRequestMail($ratingLink));
                    $emailLog = $this->emailLogService->createEmailLog(
                                    $user->id,
                                    'Rating Creation',
                                    'Rating creation email for reservation ID ' . $this->reservation->id
                                );
                    // Update reservation email_sent_at timestamp
                            $this->reservation->update(['email_sent_at' => now()]);
                    Log::info('Rating request email sent successfully.', [
                        'user_id' => $user->id,
                        'reservation_id' => $this->reservation->id,
                    ]);
                } else {
                    Log::warning('Invalid notification method. No notification sent.', [
                        'user_id' => $user->id,
                        'reservation_id' => $this->reservation->id,
                    ]);
                }
            } else {
                Log::info('User has not opted for rating notifications.', [
                    'user_id' => $user->id,
                    'reservation_id' => $this->reservation->id,
                ]);
            }

            // Update reservation email_sent_at timestamp
            $this->reservation->update(['email_sent_at' => now()]);
        } catch (\Exception $e) {
            Log::error('Failed to send rating notification.', [
                'reservation_id' => $this->reservation->id,
                'user_id' => $user->id,
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
