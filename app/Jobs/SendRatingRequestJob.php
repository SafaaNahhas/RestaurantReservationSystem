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
    Log::info('$this->reservation->user'. $this->reservation->user);
        $ratingApiLink = url('/api/ratings?reservation_id=' . $this->reservation->id . '&user_id=' . $user->id);

        Mail::to($user->email)->send(new RatingRequestMail($ratingApiLink,$user));

        Log::info('Rating email dispatched.', [
            'user_id' => $user->id,
            'reservation_id' => $this->reservation->id,
        ]);
    }
}
