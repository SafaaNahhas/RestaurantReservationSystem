<?php

namespace App\Jobs;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\EmailLog;
use App\Models\Reservation;
use App\Services\EmailLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendManagerDailyReservationReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected EmailLogService $emailLogService;

    /**
     * Create a new job instance.
     */
    public function __construct(EmailLogService $emailLogService)
    {
        $this->emailLogService = $emailLogService;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
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

                try {
                    Mail::send('emails.manager_daily_reservation_report', $data, function ($message) use ($manager) {
                        $message->to($manager->email)
                                ->subject('Daily Reservations Report for Your Department');
                    });

                    $emailLog = $this->emailLogService->createEmailLog(
                        $manager->id,
                        'Manger Daily Reservations',
                        'Daily Reservation Report in '.now()
                    );
                } catch (\Exception $e) {
                    Log::error('Error in sending email to manager ' . $manager->id . ': ' . $e->getMessage());
                    $this->emailLogService->updateEmailLog(
                        $emailLog,
                        'Failed to send Daily Reservation Report to manager ' . $manager->id . ' at ' . now()
                    );
                }
            }

            Log::info('GenerateDailyReservationReport job completed.');
        } catch (Exception $e) {
            Log::error('Error in GenerateDailyReservationReport job: ' . $e->getMessage());
        }
    }
}
