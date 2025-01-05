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
     * Process Flow:
     * 1. Loads fresh reservation data with related table, department, and manager
     * 2. Verifies existence of table, department, and assigned manager
     * 3. Sends email notification to department manager
     * 4. Updates reservation with notification timestamp
     *
     * Relationships Chain:
     * Reservation -> Table -> Department -> Manager
     *
     * Error Handling:
     * - Handles soft-deleted tables and departments
     * - Logs warnings for missing relationships
     * - Captures and logs notification failures
     *
     * @return void
     * @throws Throwable
     */
    public function handle(): void
    {
        try {
            // This block refreshes the reservation data from the database and loads all related models
            // refresh() ensures we have the latest data
            // load() eagerly loads the relationships we need
            $this->reservation->refresh()->load([

                // Loads the table relationship, including soft-deleted tables
                // withTrashed() ensures we can still find tables that have been soft-deleted
                'table' => function ($query) {

                    $query->withTrashed(); // Include soft-deleted tables
                },

                // Loads the department relationship through table, including soft-deleted departments
                'table.department' => function ($query) {

                    $query->withTrashed(); // Include soft-deleted departments
                },

                // Loads the manager relationship through department
                'table.department.manager'
            ]);

            // Checks if the table exists for this reservation
            if (!$this->reservation->table) {
                Log::warning('Table not found for reservation', [
                    'reservation_id' => $this->reservation->id,
                    'table_id' => $this->reservation->table_id
                ]);
                return;
            }

            // Checks if the department exists for the table
            if (!$this->reservation->table->department) {
                Log::warning('Department not found for table', [
                    'reservation_id' => $this->reservation->id,
                    'table_id' => $this->reservation->table_id
                ]);
                return;
            }

            // Gets the department manager
            $departmentManager = $this->reservation->table->department->manager;

            // Checks if a manager exists for the department
            if (!$departmentManager) {
                Log::warning('No department manager found for reservation', [
                    'reservation_id' => $this->reservation->id,
                    'table_id' => $this->reservation->table_id,
                    'department_id' => $this->reservation->table->department_id
                ]);
                return;
            }

            // Send email notification to department manager
            $departmentManager->notify(new PendingReservationNotification($this->reservation));

            // Create an email log entry
            $this->emailLog = $this->emailLogService->createEmailLog(
                $departmentManager->id,
                'Reservation notification',
                "Reservation notification for " . $this->reservation->id
            );

            // Update only the email_sent_at timestamp
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
