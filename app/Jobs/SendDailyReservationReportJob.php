<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use App\Services\NotificationLogService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendDailyReservationReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected NotificationLogService $notificationLogService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(NotificationLogService $notificationLogService)
    {
        $this->notificationLogService = $notificationLogService;
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

            $pendingCount = $reservations->where('status', 'pending')->count();
            $confirmedCount = $reservations->where('status', 'confirmed')->count();
            $inServiceCount = $reservations->where('status', 'in_service')->count();
            $completedCount = $reservations->where('status', 'completed')->count();
            $cancelledCount = $reservations->where('status', 'cancelled')->count();
            $rejectedCount = $reservations->where('status', 'rejected')->count();

            $data = [
                'date' => $today->toFormattedDateString(),
                'reservations' => $reservations,
                'pendingCount' => $pendingCount,
                'confirmedCount' => $confirmedCount,
                'inServiceCount' => $inServiceCount,
                'completedCount' => $completedCount,
                'cancelledCount' => $cancelledCount,
                'rejectedCount' => $rejectedCount,
                'statusColors' => [
                    'pending' => '#FFA500',    // Orange
                    'confirmed' => '#2B7A0B',  // Green
                    'in_service' => '#3B82F6', // Blue
                    'completed' => '#059669',  // Emerald Green
                    'cancelled' => '#B31312',  // Red
                    'rejected' => '#7A1212',   // Dark Red
                ],
            ];

            $adminUsers = User::role('admin')->get();
            if ($adminUsers->isNotEmpty()) {
                foreach ($adminUsers as $user) {
                    try {
                        Mail::send('emails.daily_reservation_report', $data, function ($message) use ($user) {
                            $message->to($user->email)
                                ->subject('Daily Reservations Report');
                        });

                        $emailLog = $this->notificationLogService->createNotificationLog(
                            user_id: $user->id,
                            notification_method: 'mail',
                            reason_notification_send: ' Admin Daily Reservations',
                            description: 'Admin Daily Reservation Report in ' . now()
                        );
                    } catch (\Exception $e) {
                        Log::error('Error in sending email to user ' . $user->id . ': ' . $e->getMessage());
                        $this->notificationLogService->updateNotificationLog(
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