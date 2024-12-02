<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Mail\RatingRequestMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
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
    
        // تسجيل البيانات لمعرفة تفاصيل الحجز والمستخدم
        Log::info('Job started for sending rating email.', [
            'reservation_id' => $this->reservation->id,
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);
    
        try {
            // تسجيل محاولة الإرسال
            Log::info('Attempting to send email to user.', ['email' => $user->email]);
    
            Mail::to($user->email)->send(new RatingRequestMail());
    
            // إذا نجح الإرسال
            Log::info('Rating email sent successfully.', [
                'user_id' => $user->id,
                'reservation_id' => $this->reservation->id,
            ]);
        } catch (\Exception $e) {
            // إذا فشل الإرسال
            Log::error('Failed to send rating email.', [
                'error_message' => $e->getMessage(),
                'user_id' => $user->id,
                'reservation_id' => $this->reservation->id,
            ]);
        }
    }
    
}
