<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendDailyReservationReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {try{
        log::info('GenerateDailyReservationReport job started.');
        $today = Carbon::today();
        $reservations = Reservation::with(['user', 'table', 'manager'])->whereDate('created_at', $today)->get();

        $data = [
            'date' => $today->toFormattedDateString(),
            'reservations' => $reservations,
        ];

        Mail::send('emails.daily_reservation_report', $data, function ($message) {
            $message->to('hiba11h2h@gmail.com') 
                    ->subject('Daily Reservations Report');
        });
        log::info('GenerateDailyReservationReport job completed..');
    }catch (\Exception $e) {
        Log::error('Error in GenerateDailyReservationReport job: ' . $e->getMessage());
    }
    }
}