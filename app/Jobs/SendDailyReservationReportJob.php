<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use App\Services\EmailLogService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendDailyReservationReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected EmailLogService $emailLogService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EmailLogService $emailLogService)
    {
        $this->emailLogService = $emailLogService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info('GenerateDailyReservationReport job started.');
            $today = Carbon::today();
            $reservations = Reservation::with(['user', 'table', 'manager'])
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

            $adminUsers = User::role('admin')->get();
            if ($adminUsers->isNotEmpty()) {
                foreach ($adminUsers as $user) {
                    try {
                        Mail::send('emails.daily_reservation_report', $data, function ($message) use ($user) {
                            $message->to($user->email)
                                    ->subject('Daily Reservations Report');
                        });

                        $emailLog = $this->emailLogService->createEmailLog(
                            $user->id,
                            ' Admin Daily Reservations',
                            'Admin Daily Reservation Report in '.now()
                        );

                    } catch (\Exception $e) {
                        Log::error('Error in sending email to user ' . $user->id . ': ' . $e->getMessage());
                        $this->emailLogService->updateEmailLog(
                            $emailLog,
                            'Adminc Daily Reservation Report in ' . now()
                        );
                    }
                }
            } else {
                Log::warning('No admin users found to send the report to.');
            }

            Log::info('GenerateDailyReservationReport job completed.');
        } catch (\Exception $e) {
            Log::error('Error in GenerateDailyReservationReport job: ' . $e->getMessage());
        }
    }
}
