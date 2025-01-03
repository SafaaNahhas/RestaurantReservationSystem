<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendManagerDailyReservationReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {  try {
        Log::info('GenerateDailyReservationReport job started.');

        $today = Carbon::today();
        $managers = User::role('reservation manager')->get(); 

        foreach ($managers as $manager) {
            $reservations = Reservation::with(['user', 'table'])
                ->where('manager_id', $manager->id)
                ->whereDate('created_at', $today)
                ->get();

            $confirmedCount = $reservations->where('status', 'confirmed')->count();
            $cancelledCount = $reservations->where('status', 'cancelled')->count();
            $pendingCount = $reservations->where('status', 'pending')->count();

            $data = [
                'date' => $today->toFormattedDateString(),
                'reservations' => $reservations,
                'confirmedCount' => $confirmedCount,
                'cancelledCount' => $cancelledCount,
                'pendingCount' => $pendingCount,
            ];

            Mail::send('emails.manager_daily_reservation_report', $data, function ($message) use ($manager) {
                $message->to($manager->email) 
                        ->subject('Daily Reservations Report for Your Department');
            });
        }

        Log::info('GenerateDailyReservationReport job completed.');
    } catch (Exception $e) {
        Log::error('Error in GenerateDailyReservationReport job: ' . $e->getMessage());
    }

    }
}
