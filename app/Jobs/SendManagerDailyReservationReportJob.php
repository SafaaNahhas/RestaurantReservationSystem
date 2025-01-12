<?php

namespace App\Jobs;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\EmailLog;
use App\Models\Reservation;
use App\Services\NotificationLogService;
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

    protected NotificationLogService $notificationLogService;

    /**
     * Create a new job instance.
     */
    public function __construct(NotificationLogService $notificationLogService)
    {
        $this->notificationLogService = $notificationLogService;
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

                try {
                    Mail::send('emails.manager_daily_reservation_report', $data, function ($message) use ($manager) {
                        $message->to($manager->email)
                            ->subject('Daily Reservations Report for Your Department');
                    });

                    $emailLog = $this->notificationLogService->createNotificationLog(
                        user_id: $manager->id,
                        notification_method: 'mail',
                        reason_notification_send: 'Manger Daily Reservations',
                        description: 'Daily Reservation Report in ' . now()
                    );
                } catch (\Exception $e) {
                    Log::error('Error in sending email to manager ' . $manager->id . ': ' . $e->getMessage());
                    $this->notificationLogService->updateNotificationLog(
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