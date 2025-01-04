<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Models\User;
use App\Notifications\PendingReservationNotification;
use App\Services\EmailLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotifyManagersAboutReservation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Reservation $reservation;
    protected EmailLogService $emailLogService;
    protected $emailLog; // Added this to hold the email log for access in failed()

    /**
     * Create a new job instance.
     *
     * @param Reservation $reservation The reservation that needs manager notification
     * @param EmailLogService $emailLogService The service responsible for logging email notifications
     */
    public function __construct(Reservation $reservation, EmailLogService $emailLogService)
    {
        $this->reservation = $reservation;
        $this->emailLogService = $emailLogService;  // Fixed missing assignment
    }

    /**
     * Execute the notification job for a new reservation.
     *
     * @return void
     * @throws Throwable
     */
    public function handle(): void
    {
        try {
            // Refresh reservation data and load necessary relationships
            $this->reservation->refresh()->load([

                'table' => function ($query) {
                    $query->withTrashed(); // Include soft-deleted tables
                },

                'table.department' => function ($query) {
                    $query->withTrashed(); // Include soft-deleted departments
                },

                'table.department.manager'
            ]);

            // Validate table, department, and manager existence
            if (!$this->reservation->table) {
                Log::warning('Table not found for reservation', [
                    'reservation_id' => $this->reservation->id,
                    'table_id' => $this->reservation->table_id
                ]);
                return;
            }

            if (!$this->reservation->table->department) {
                Log::warning('Department not found for table', [
                    'reservation_id' => $this->reservation->id,
                    'table_id' => $this->reservation->table_id
                ]);
                return;
            }

            // Get the department manager
            $departmentManager = $this->reservation->table->department->manager;

            if (!$departmentManager) {
                Log::warning('No department manager found for reservation', [
                    'reservation_id' => $this->reservation->id,
                    'table_id' => $this->reservation->table_id,
                    'department_id' => $this->reservation->table->department_id
                ]);
                return;
            }

            // Send the notification to the department manager
            $departmentManager->notify(new PendingReservationNotification($this->reservation));

            // Create an email log entry
            $this->emailLog = $this->emailLogService->createEmailLog(
                $departmentManager->id,
                'Reservation notification',
                "Reservation notification for " . $this->reservation->id
            );

            // Update the reservation's email_sent_at timestamp
            $this->reservation->update([
                'email_sent_at' => now()
            ]);

            Log::info('Reservation notification sent to department manager', [
                'reservation_id' => $this->reservation->id,
                'manager_id' => $departmentManager->id,
                'department_id' => $this->reservation->table->department_id
            ]);

        } catch (Throwable $e) {
            Log::error('Error sending notification to department manager', [
                'reservation_id' => $this->reservation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        // Log the failure
        Log::error('Failed to send reservation notifications', [
            'reservation_id' => $this->reservation->id,
            'error' => $exception->getMessage()
        ]);

        // Update the email log on failure
        if (isset($this->emailLog)) {  // Make sure $emailLog exists
            $this->emailLogService->updateEmailLog(
                $this->emailLog,
                "Reservation notification for " . $this->reservation->id
            );
        }
    }
}
