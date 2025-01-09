<?php

namespace App\Services;

use App\Jobs\NotifyManagersAboutReservation;
use App\Notifications\PendingReservationNotification;
use Exception;
use Carbon\Carbon;
use App\Models\Table;
use App\Models\EmailLog;
use App\Models\Reservation;
use App\Services\EmailLogService;
use App\Models\ReservationLog;
use Illuminate\Http\JsonResponse;
use App\Jobs\SendRatingRequestJob;
use Illuminate\Support\Facades\Log;
use App\Mail\ReservationDetailsMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationRejectedMail;
use App\Mail\ReservationCancelledMail;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\Reservation\TableReservationResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReservationService
{
    protected $emailLogService;
    public function __construct(EmailLogService $emailLogService)
    {
        $this->emailLogService = $emailLogService;
    }

    /**
     * Store a new reservation.
     *
     * This method handles the logic for storing a reservation, validating the
     * reservation times, checking for table availability, and saving the reservation.
     * If the table is not available or the reservation duration exceeds the limit,
     * the method will return a corresponding error response.
     *
     * @param array $data Reservation data, including the start and end date, table number, and guest count.
     * @return array The result of the reservation operation, including status code and message.
     */
    public function storeReservation(array $data)
    {
        try {
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);
            // Validate reservation dates
            if ($startDate->greaterThan(Carbon::now()->addWeeks(2))) {
                return [
                    'status_code' => 422,
                    'message' => 'Reservations cannot be made for dates more than two weeks from today.'
                ];
            }
            if ($startDate->diffInHours($endDate) > 6 || !$startDate->isSameDay($endDate)) {
                return [
                    'status_code' => 422,
                    'message' => 'Reservations must not exceed 6 hours and must be within the same day.'
                ];
            }

            // Fetch the table with department and manager relationships
            $selectedTable = Table::with('department.manager')
                ->when(isset($data['table_number']), function ($query) use ($data) {
                    return $query->where('table_number', $data['table_number']);
                }, function ($query) use ($data) {
                    return $query->where('seat_count', '>=', $data['guest_count'])
                                ->orderBy('seat_count', 'asc');
                })
                ->whereDoesntHave('reservations', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($nestedQuery) use ($startDate, $endDate) {
                            $nestedQuery->where('start_date', '<', $startDate)
                                        ->where('end_date', '>', $endDate);
                        });
                })
                ->select(['id', 'table_number', 'seat_count', 'department_id'])
                ->first();

            // Handle table availability and department/manager validations
            if (!$selectedTable && Table::where('seat_count', '>=', $data['guest_count'])->doesntExist()) {
                return [
                    'status_code' => 422,
                    'message' => 'No tables are available to accommodate the required number of guests reserved. Consider booking multiple tables to accommodate your group.',
                ];
            }
            if (!$selectedTable) {
                $reservedTables = Table::whereHas('reservations', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($nestedQuery) use ($startDate, $endDate) {
                            $nestedQuery->where('start_date', '<', $startDate)
                                        ->where('end_date', '>', $endDate);
                        });
                })
                    ->with('reservations')
                    ->select(['id', 'table_number', 'seat_count'])
                    ->get();

                return [
                    'status_code' => 409,
                    'message' => isset($data['table_number'])
                        ? 'Selected table is not available for the selected time.'
                        : 'No available tables for the selected time.',
                    'reserved_tables' => $reservedTables
                ];
            }

            // Ensure the table has a department and a manager
            if (!$selectedTable->department) {
                return [
                    'status_code' => 422,
                    'message' => 'The table does not belong to any department.',
                ];
            }
            if (!$selectedTable->department->manager) {
                return [
                    'status_code' => 422,
                    'message' => 'The department does not have a manager.',
                ];
            }

            // Create the reservation
            $reservation = Reservation::create([
                'user_id' => auth()->id(),
                'table_id' => $selectedTable->id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'guest_count' => $data['guest_count'],
                'services' => $data['services'] ?? null,
                'status' => 'pending',
                'manager_id' => $selectedTable->load('department.manager')->department->manager->id ?? null,

            ]);

            // Notify managers
            NotifyManagersAboutReservation::dispatch($reservation, $this->emailLogService);

            // Get the department and manager details
            $department = $selectedTable->department;
            $manager = $department->manager;

            // Log the department and manager information
            Log::info('Reservation made for table in department', [
                'department_name' => $department->name,
                'manager_name' => $manager->name,
                'manager_email' => $manager->email,
            ]);
            // Notify managers
            NotifyManagersAboutReservation::dispatch($reservation);

            // Return success response
            return [
                'status_code' => 201,
                'message' => 'Reservation created successfully',
                'reservation' => $reservation,
                'department' => [
                    'name' => $department->name,
                ],
                'manager' => [
                    'name' => $manager->name,
                    'email' => $manager->email,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Error storing reservation: ' . $e->getMessage());
            return [
                'status_code' => 500,
                'message' => 'An unexpected error occurred.'
            ];
        }
    }

    /**
     * Update an existing reservation with new details.
     *
     * @param array $data The data to update the reservation with.
     * @param int $reservationId The ID of the reservation to update.
     * @return array Response with status, message, and additional data if applicable.
     */
    public function updateReservation(array $data, $reservationId)
    {
        try {
            // Find the reservation by ID
            $reservation = Reservation::find($reservationId);
            // Check if the reservation exists
            if (!$reservation) {
                return [
                    'status_code' => 404,
                    'message' => 'Reservation not found.'
                ];
            }
            // Parse and validate the start and end dates
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);
            // Restrict updates to dates within the next two weeks
            if ($startDate->greaterThan(Carbon::now()->addWeeks(2))) {
                return [
                    'status_code' => 422,
                    'message' => 'Reservations cannot be updated for dates more than two weeks from today.'
                ];
            }
            // Ensure the reservation duration does not exceed 6 hours and is on the same day
            if ($startDate->diffInHours($endDate) > 6 || !$startDate->isSameDay($endDate)) {
                return [
                    'status_code' => 422,
                    'message' => 'Reservations must not exceed 6 hours and must be within the same day.'
                ];
            }
            // Find a suitable table for the reservation
            $selectedTable = Table::when(isset($data['table_number']), function ($query) use ($data) {
                return $query->where('table_number', $data['table_number']);
            }, function ($query) use ($data) {
                return $query->where('seat_count', '>=', $data['guest_count'])
                    ->orderBy('seat_count', 'asc');
            })
                ->whereDoesntHave('reservations', function ($query) use ($startDate, $endDate, $reservationId) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($nestedQuery) use ($startDate, $endDate) {
                            $nestedQuery->where('start_date', '<', $startDate)
                                        ->where('end_date', '>', $endDate);
                        })
                        ->where('id', '!=', $reservationId);
                })
                ->select(['id', 'table_number', 'seat_count'])
                ->first();

            // Validate table seat count
            if ($selectedTable && $selectedTable->seat_count < $data['guest_count']) {
                return [
                    'status_code' => 422,
                    'message' => 'The selected table does not have enough seats for the number of guests.'
                ];
            }
            // If no table is available, return a conflict response
            if (!$selectedTable && Table::where('seat_count', '>=', $data['guest_count'])->doesntExist()) {
                return [
                    'status_code' => 422,
                    'message' => 'No tables are available to accommodate the required number of guests reserved. Consider booking multiple tables to accommodate your group.',
                ];
            }

            if (!$selectedTable) {
                $reservedTables = Table::whereHas('reservations', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($nestedQuery) use ($startDate, $endDate) {
                            $nestedQuery->where('start_date', '<', $startDate)
                                        ->where('end_date', '>', $endDate);
                        });
                })
                    ->with('reservations')
                    ->select(['id', 'table_number', 'location', 'seat_count', 'department_id'])
                    ->get();

                return [
                    'status_code' => 409,
                    'message' => isset($data['table_number'])
                        ? 'Selected table is not available for the selected time.'
                        : 'No available tables for the selected time.',
                    'reserved_tables' => $reservedTables
                ];
            }
            // Update reservation details
            $reservation->update([
                'table_id' => $selectedTable->id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'guest_count' => $data['guest_count'],
                'services' => $data['services'] ?? null,
            ]);

            return [
                'status_code' => 200,
                'message' => 'Reservation updated successfully.',
                'reservation' => $reservation
            ];
        } catch (Exception $e) {
            // Log unexpected errors
            Log::error('Error updating reservation: ' . $e->getMessage());
            return [
                'status_code' => 500,
                'message' => 'An unexpected error occurred.'
            ];
        }
    }


    /**
     * Get all tables with their reservations.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTablesWithReservations(array $filter = [])
    {
        $tables= Table::whereHas('reservations', function ($query) use ($filter) {
            if (isset($filter['status'])) {
                $query->whereIn('status', (array)$filter['status']);
            }
        })
        ->with(['reservations' => function ($query) use ($filter) {
            $query->select('id', 'table_id', 'start_date', 'end_date', 'status');

            if (isset($filter['status'])) {
                $query->whereIn('status', (array)$filter['status']);
            }
        }])
        ->get();

        return $tables;
    }

    /**
     * Service method to confirm a reservation.
     *
     * @param int $reservationId
     * @return array
     */
    public function confirmReservation($reservationId)
    {
        try {
            $reservation = Reservation::with([
                'user:id,name,email',
                'table:id,table_number'
            ])->select('id', 'user_id', 'table_id', 'start_date', 'end_date', 'status')
            ->findOrFail($reservationId);

            if ($reservation->status !== 'pending') {
                return [
                    'error' => true,
                    'message' => 'Reservation must be in pending state to confirm',
                ];
            }

            if (Carbon::parse($reservation->start_date)->isPast()) {
                return [
                    'error' => true,
                    'message' => 'Cannot modify past reservations',
                ];
            }

            $reservation->update(['status' => 'confirmed']);

            Log::info("Reservation table number before sending email: " . $reservation->table->table_number);

            Mail::to($reservation->user->email)
                ->queue(new ReservationDetailsMail($reservation));

            // Create email log
            $emailLog = $this->emailLogService->createEmailLog(
                $reservation->user->id,
                'reservation confirmed',  // Fixed typo
                "Email to confirm reservation ID " . $reservation->id,
            );

            return [
                'error' => false,
                'reservation' => $reservation,
            ];
        } catch (ModelNotFoundException $e) {
            Log::warning("Reservation with ID {$reservationId} not found.");
            return [
                'error' => true,
                'message' => "No reservation found with ID {$reservationId}",
            ];
        } catch (Exception $e) {
            Log::error('Error confirming reservation: ' . $e->getMessage());

            // Ensure the email log variable is available
            if (isset($emailLog)) {
                $this->emailLogService->updateEmailLog(
                    $emailLog,
                    "Email to confirm reservation ID " . $reservation->id . " failed.",
                );
            }

            return [
                'error' => true,
                'message' => 'An unexpected error occurred.',
            ];
        }
    }


    /**
     * Reject a reservation.
     *
     * @param int $reservationId
     * @param string $rejectionReason
     * @return array
     */
    public function rejectReservation($reservationId, string $rejectionReason): array
    {
        try {
            // Find the reservation or throw an exception if not found
            $reservation = Reservation::with('user', 'table')->findOrFail($reservationId);
            if ($reservation->status !== 'pending') {
                return [
                    'error' => true,
                    'message' => 'Reservation must be in pending state to rejecte',
                ];
            }

            if (Carbon::parse($reservation->start_date)->isPast()) {
                return [
                    'error' => true,
                    'message' => 'Cannot modify past reservations',
                ];
            }


            // Update the reservation status to 'rejected' and store the rejection reason

            $reservation->update(['status' => 'rejected']);
            $reservation->details()->create([
            'status' => 'rejected', // Record the status as rejected
            'rejection_reason' => $rejectionReason, // Store the rejection reason
                        ]);

            // Log the rejection
            Log::info("Reservation ID {$reservation->id} rejected by User ID {$reservation->user_id}. Reason: {$rejectionReason}");

            // Send rejection email (queue for async processing)
            Mail::to($reservation->user->email)
                ->queue(new ReservationRejectedMail($reservation));

            return [
                'error' => false,
                'message' => 'Reservation rejected successfully',
                'data' => [
                    'reservation_id' => $reservation->id,
                    'table_number' => $reservation->table->table_number,
                    'start_date' => $reservation->start_date,
                    'end_date' => $reservation->end_date,
                    'rejection_reason' => $rejectionReason,
                ],
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'error' => true,
                'message' => "No reservation found with ID {$reservationId}",
                'data' => null,
            ];
        } catch (Exception $e) {
            Log::error('Error rejecting reservation: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'An unexpected error occurred.',
            ];
        }
    }
    /**
     * Cancel a reservation.
     *
     * @param int $reservationId
     * @return array
     */
    public function cancelReservation($reservationId, string $cancellationReason): array
    {
        try {
            // Find the reservation or throw an exception if not found
            $reservation = Reservation::with('user', 'table')->findOrFail($reservationId);

            if ($reservation->status !== 'pending') {
                return [
                    'error' => true,
                    'message' => 'Reservation must be in pending state to cancelled',
                ];
            }

            if (Carbon::parse($reservation->start_date)->isPast()) {
                return [
                    'error' => true,
                    'message' => 'Cannot modify past reservations',
                ];
            }
            $reservation->update(['status' => 'cancelled']);

            $reservation->details()->create([
            'status' => 'cancelled',        // Record the status as cancelled
            'cancelled_at' => now(),           // Store the timestamp of cancellation
            'cancellation_reason' => $cancellationReason,  // Store the cancellation reason
            ]);

            // Log the cancellation
            Log::info("Reservation ID {$reservation->id} cancelled by User ID {$reservation->user_id}. Reason: {$cancellationReason}");
            // Send cancellation email (queue for async processing)
            Mail::to($reservation->user->email)
                ->queue(new ReservationCancelledMail($reservation, false));
            // Fetch the department manager
            $manager = $reservation->table->department->manager;
            // Send cancellation email to the manager
            if ($manager) {
                Mail::to($manager->email)
                    ->queue(new ReservationCancelledMail($reservation, true)); // Pass `true` for the manager
            }
            return [
                    'error' => false,
                    'message' => 'Reservation cancelled successfully',
                    'data' => [
            'reservation_id' => $reservation->id,
            'table_number' => $reservation->table->table_number,
            'start_date' => $reservation->start_date,
            'end_date' => $reservation->end_date,
            'cancellation_reason' => $cancellationReason,
                    ],
                ];
        } catch (ModelNotFoundException $e) {
            Log::warning("Reservation with ID {$reservationId} not found.");
            return [
                'error' => true,
                'message' => "No reservation found with ID {$reservationId}",
            ];
        } catch (Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return [
                'error' => true,
                'message' => 'An unexpected error occurred. ' . $e->getMessage(),
            ];
        }

    }

    /**
     * Start service for a reservation.
     *
     * @param int $reservationId
     * @return array
     */
    public function startService($reservationId)
    {
        try {
            $reservation = Reservation::find($reservationId);

            if (!$reservation) {
                return ['error' => true, 'message' => "No reservation found with ID {$reservationId}"];
            }

            // Ensure the reservation is confirmed before starting the service
            if ($reservation->status !== 'confirmed') {
                return ['error' => true, 'message' => 'Reservation must be confirmed to start service'];
            }

            // Start the service
            $reservation->update([
                'status' => 'in_service',
                'started_at' => now(),
            ]);

            return [
                'error' => false,
                'reservation' => $reservation,
            ];
        } catch (Exception $e) {
            // Log any errors and return an error message
            Log::error('Error starting service: ' . $e->getMessage());
            return ['error' => true, 'message' => 'An unexpected error occurred.'];
        }
    }

    /**
     * Complete service for a reservation.
     *
     * @param int $reservationId
     * @return array
     */
    public function completeService($reservationId)
    {
        try {
            $reservation = Reservation::find($reservationId);

            if (!$reservation) {
                return ['error' => true, 'message' => "No reservation found with ID {$reservationId}"];
            }

            // Ensure the reservation is in service before completing it
            if ($reservation->status !== 'in_service') {
                return ['error' => true, 'message' => 'Reservation must be in service to complete'];
            }

            // Complete the service
            $reservation->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return [
                'error' => false,
                'reservation' => $reservation,
            ];
        } catch (Exception $e) {
            // Log any errors and return an error message
            Log::error('Error completing service: ' . $e->getMessage());
            return ['error' => true, 'message' => 'An unexpected error occurred.'];
        }
    }

    /**
     * Soft delete a reservation based on specific conditions.
     *
     * @param int $reservationId The ID of the reservation to soft delete.
     * @return array Response indicating success or failure.
     */
    public function softDeleteReservation($reservationId)
    {
        try {
            // Retrieve the reservation
            $reservation = Reservation::findOrFail($reservationId);

            // Ensure the reservation meets the conditions for soft deletion
            if (
                !in_array($reservation->status, ['completed', 'rejected','cancelled']) &&
                $reservation->end_date > now()
            ) {
                return [
                    'error' => true,
                    'message' => 'Soft delete is only allowed for completed, rejected,
                    cancelled or past reservations.',
                ];
            }
            // Perform the soft delete
            $reservation->delete();
            Log::info("Reservation with ID {$reservationId} has been soft deleted.");

            return [
                'error' => false,
                'message' => 'Reservation soft deleted successfully',
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'error' => true,
                'message' => "No reservation found with ID {$reservationId}",
            ];
        } catch (Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return [
                'error' => true,
                'message' => 'An unexpected error occurred. ' . $e->getMessage(),
            ];
        }
    }
    /**
     * Permanently delete a soft-deleted reservation.
     *
     * @param int $reservationId The ID of the reservation to force delete.
     * @return array Response indicating success or failure.
     */
    public function forceDeleteReservation($reservationId)
    {
        try {
            // Retrieve the soft-deleted reservation
            $reservation = Reservation::withTrashed()->findOrFail($reservationId);

            // Ensure the reservation is soft deleted
            if (!$reservation->trashed()) {
                return [
                    'error' => true,
                    'message' => 'Force delete is only allowed for soft-deleted reservations.',
                ];
            }
            // Perform the force delete
            $reservation->forceDelete();
            Log::info("Reservation with ID {$reservationId} has been force deleted.");

            return [
                'error' => false,
                'message' => 'Reservation force deleted successfully',
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'error' => true,
                'message' => "No reservation found with ID {$reservationId}",
            ];
        } catch (Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return [
                'error' => true,
                'message' => 'An unexpected error occurred. ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Restore a soft-deleted reservation.
     *
     * @param int $reservationId The ID of the reservation to restore.
     * @return array Response indicating success or failure.
     */
    public function restoreReservation($reservationId)
    {
        try {
            // Retrieve the soft-deleted reservation
            $reservation = Reservation::onlyTrashed()->findOrFail($reservationId);
            // Restore the reservation
            $reservation->restore();

            return [
                'error' => false,
                'message' => 'Reservation restored successfully',
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'error' => true,
                'message' => "No soft-deleted reservation found with ID {$reservationId}",
            ];
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => 'An unexpected error occurred.',
            ];
        }
    }

    /**
    * Retrieve all soft-deleted reservations.
     *
     * @return array Response containing soft-deleted reservations or an error message.
     */
    public function getSoftDeletedReservations()
    {
        try {
            // Retrieve all soft-deleted reservations
            $reservations = Reservation::onlyTrashed()->get(); // عرض جميع الحجوزات المحذوفة ناعماً

            return [
                'error' => false,
                'reservations' => $reservations,
            ];
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => 'An unexpected error occurred.',
            ];
        }
    }
}
