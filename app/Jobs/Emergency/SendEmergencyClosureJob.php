<?php

namespace App\Jobs\Emergency;

use Carbon\Carbon;
use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use App\Services\EmailLogService;
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
    protected EmailLogService $emailLogService;

   /**
     * Create a new job instance.
     */
    public function __construct($affectedReservations, EmailLogService $emailLogService)
    {
        $this->emailLogService = $emailLogService;
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
            try {
                $notificationSettings = $reservation->user->notificationSettings;
                if ($notificationSettings->method_send_notification == "telegram") {
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
                } else {
                    Mail::to($reservation->user->email)
                        ->send(new EmergencyClosureMail($restaurant, $reservation));
                    // Log the email success
                    $emailLog = $this->emailLogService->createEmailLog(
                        $reservation->user->id,
                        'Emergency Closure Mail',
                        'Reservation in ' . $reservation->start_date
                    );
                }
            } catch (\Exception $e) {
                // Log the email failure
                Log::error('Error sending email to user ' . $reservation->user->id . ': ' . $e->getMessage());
                $this->emailLogService->updateEmailLog(
                    $emailLog,
                    'Reservation in ' . $reservation->start_date
                );
            }
        }
    }
    
}
