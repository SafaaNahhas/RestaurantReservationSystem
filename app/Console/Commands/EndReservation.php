<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;
use App\Jobs\SendRatingRequestJob;
use Illuminate\Support\Facades\Log;

class EndReservation extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:end-reservation';

    /**
     * The console command description.
     */
    protected $description = 'Send rating email to users whose reservations have ended';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startOfMinute = now()->setTimezone('Asia/Damascus')->startOfMinute();
        $endOfMinute = now()->setTimezone('Asia/Damascus')->endOfMinute();

        $reservations = Reservation::whereBetween('end_date', [$startOfMinute, $endOfMinute])
            ->where('status', 'confirmed')
            ->get();


        foreach ($reservations as $reservation) {
            SendRatingRequestJob::dispatch($reservation);

            Log::info('Rating email dispatched.', [
                'reservation_id' => $reservation->id,
                'user_email' => $reservation->user->email,
            ]);

            $this->info('Rating email sent for reservation ID: ' . $reservation->id);
        }
    }
}
