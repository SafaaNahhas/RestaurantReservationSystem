<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use App\Mail\RatingRequestMail;
use App\Services\EmailLogService;
use Illuminate\Support\Facades\Log;
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
     * Sends a rating request email to the user associated with the reservation.
     */
    public function handle()
    {
        $user = $this->reservation->user;

        Log::info('Job started for sending rating email.', [
            'reservation_id' => $this->reservation->id,
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);

        try {
            Log::info('Attempting to send email to user.', ['email' => $user->email]);

            // Create links for the rating request email
            $createLink = url("/api/rating?reservation_id={$this->reservation->id}&user_id={$user->id}");

            // Send the rating request email
            Mail::to($user->email)->send(new RatingRequestMail($createLink));

            // Create a log entry for the sent email
            $emailLog = $this->emailLogService->createEmailLog(
                $user->id,
                'Rating Creation',
                'Rating creation email for reservation ID ' . $this->reservation->id
            );

            Log::info('Rating email sent successfully.', [
                'user_id' => $user->id,
                'reservation_id' => $this->reservation->id,
            ]);

            // Update reservation email_sent_at timestamp
            $this->reservation->update(['email_sent_at' => now()]);
        } catch (\Exception $e) {
            // Update email log status to 'failed' in case of error
            $this->emailLogService->updateEmailLog(
                $emailLog,
                'Reservation ID ' . $this->reservation->id . ' email failed to send to ' . $user->email
            );

            Log::error('Failed to send email.', [
                'reservation_id' => $this->reservation->id,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
