<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Mail\RatingRequestMail;

use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendRatingRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reservation;

    /**
     * Create a new job instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Execute the job.
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

            $createLink = url("/api/rating?reservation_id={$this->reservation->id}&user_id={$user->id}");
            $viewLink = url("/api/rating/{$this->reservation->id}");
            Mail::to($user->email)->send(new RatingRequestMail($createLink, $viewLink));

            Log::info('Rating email sent successfully.', [
                'user_id' => $user->id,
                'reservation_id' => $this->reservation->id,
            ]);
            $this->reservation->update(['email_sent_at' => now()]);
        } catch (\Exception $e) {
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
