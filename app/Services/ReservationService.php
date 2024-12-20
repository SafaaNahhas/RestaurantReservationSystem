<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Table;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\TableReservationResource;
use Illuminate\Pagination\LengthAwarePaginator;

class ReservationService
{
    /**
     * Store a new reservation.
     * @param array $data
     * @return array
     */
    public function storeReservation(array $data)
    {
        try {
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);
            $services = $data['services'] ?? null;

            if ($startDate->diffInHours($endDate) > 6 || !$startDate->isSameDay($endDate)) {
                return [
                    'status_code' => 422,
                    'message' => 'Reservations must not exceed 6 hours and must be within the same day.'
                ];
            }

            $tablesWithEnoughSeats = Table::where('seat_count', '>=', $data['guest_count'])->exists();

            if (!$tablesWithEnoughSeats) {
                return [
                    'status_code' => 404,
                    'message' => 'No tables available with enough seats for the guest count.'
                ];
            }

            $availableTable = Table::where('seat_count', '>=', $data['guest_count'])
                ->whereDoesntHave('reservations', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($nestedQuery) use ($startDate, $endDate) {
                            $nestedQuery->where('start_date', '<', $startDate)
                                ->where('end_date', '>', $endDate);
                        });
                })
                ->first();

            if (!$availableTable) {
                $reservedTables = Table::where('seat_count', '>=', $data['guest_count'])
                    ->whereHas('reservations', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function ($nestedQuery) use ($startDate, $endDate) {
                                $nestedQuery->where('start_date', '<', $startDate)
                                    ->where('end_date', '>', $endDate);
                            });
                    })
                    ->with('reservations')
                    ->paginate(10);

                return [
                    'status_code' => 409,
                    'message' => 'No available tables for the selected time.',
                    'reserved_tables' => $reservedTables
                ];
            }

            $reservation = Reservation::with('table')->create([
                'user_id' => auth()->id(),
                'table_id' => $availableTable->id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'guest_count' => $data['guest_count'],
                'services' => $services,
                'status' => 'pending',
            ]);
            Log::debug('Reservation table:', ['table' => $reservation->table]);

            return [
                'status_code' => 201,
                'message' => 'Reservation created successfully',
                'reservation' => $reservation
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
     * Get reserved tables during a specific time period.
     * @param int $guestCount
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getReservedTables(int $guestCount, string $startDate, string $endDate)
    {
        return Table::where('seat_count', '>=', $guestCount)
            ->whereHas('reservations', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($nestedQuery) use ($startDate, $endDate) {
                        $nestedQuery->where('start_date', '<', $startDate)
                            ->where('end_date', '>', $endDate);
                    });
            })
            ->with(['reservations' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($nestedQuery) use ($startDate, $endDate) {
                        $nestedQuery->where('start_date', '<', $startDate)
                            ->where('end_date', '>', $endDate);
                    });
            }, 'table'])
            ->get();
    }

    /**
     * Check for table conflicts
     * @param int $tableId
     * @param string $startDate
     * @param string $endDate
     * @return bool
     */

    public function tableHasConflict(int $tableId, string $startDate, string $endDate): bool
    {
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        Log::info('Checking for conflicts', [
            'table_id' => $tableId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return Reservation::where('table_id', $tableId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($nestedQuery) use ($startDate, $endDate) {
                        $nestedQuery->where('start_date', '<', $startDate)
                            ->where('end_date', '>', $endDate);
                    });
            })
            ->exists();
    }





    /**
     * Service method to cancel unconfirmed reservations.
     *
     * @return array
     */
    public function cancelUnconfirmedReservations()
    {
        try {
            $currentTime = Carbon::now();

            // Retrieve all unconfirmed reservations
            $reservations = Reservation::where('status', 'pending')
                ->where('start_date', '>', $currentTime) // ensure that the reservation start date is in the future
                ->get();

            // If no reservations are found to cancel
            if ($reservations->isEmpty()) {
                return ['error' => true, 'message' => 'No unconfirmed reservations found to cancel.'];
            }

            // Cancel the reservations
            foreach ($reservations as $reservation) {
                $reservation->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

                // Log the cancellation
                Log::info('Cancelled reservation ID: ' . $reservation->id . ' by User ID: ' . $reservation->user_id);
            }

            // Return cancelled reservations data
            return [
                'error' => false,
                'message' => 'The following reservations have been cancelled:',
                'cancelled_reservations' => $reservations->map(function ($reservation) {
                    return [
                        'reservation_id' => $reservation->id,
                        'user_id' => $reservation->user_id,
                        'start_date' => $reservation->start_date,
                        'end_date' => $reservation->end_date,
                    ];
                }),
            ];
        } catch (Exception $e) {
            // Log any errors and return an error message
            Log::error('Error canceling unconfirmed reservations: ' . $e->getMessage());
            return ['error' => true, 'message' => 'An unexpected error occurred.'];
        }
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
            // Find the reservation by ID
            $reservation = Reservation::find($reservationId);

            // If reservation not found
            if (!$reservation) {
                return ['error' => true, 'message' => "No reservation found with ID {$reservationId}"];
            }

            // If the reservation is not in pending status
            if ($reservation->status !== 'pending') {
                return ['error' => true, 'message' => 'Reservation must be in pending state to confirm'];
            }

            // If the reservation is past its start date
            if (Carbon::parse($reservation->start_date)->isPast()) {
                return ['error' => true, 'message' => 'Cannot modify past reservations'];
            }

            // Update reservation status to confirmed
            $reservation->update(['status' => 'confirmed']);

            // Return the updated reservation data
            return [
                'error' => false,
                'reservation' => $reservation,
            ];
        } catch (Exception $e) {
            // Log the error and return a generic error message
            Log::error('Error confirming reservation: ' . $e->getMessage());
            return ['error' => true, 'message' => 'An unexpected error occurred.'];
        }
    }
    /**
     * Cancel a reservation.
     *
     * @param int $reservationId
     * @return array
     */
    public function cancelReservation($reservationId)
    {
        try {
            $reservation = Reservation::find($reservationId);

            if (!$reservation) {
                return ['error' => true, 'message' => "No reservation found with ID {$reservationId}"];
            }

            // Cancel the reservation
            $reservation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Log the cancellation
            Log::info('Cancelled reservation ID: ' . $reservation->id . ' by User ID: ' . $reservation->user_id);

            return [
                'error' => false,
                'message' => 'Reservation cancelled successfully',
            ];
        } catch (Exception $e) {
            // Log any errors and return an error message
            Log::error('Error canceling reservation: ' . $e->getMessage());
            return ['error' => true, 'message' => 'An unexpected error occurred.'];
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
     * Hard delete a reservation.
     *
     * @param int $reservationId
     * @return array
     */
    public function hardDeleteReservation($reservationId)
    {
        try {
            $reservation = Reservation::find($reservationId);

            if (!$reservation) {
                return ['error' => true, 'message' => "No reservation found with ID {$reservationId}"];
            }

            // Perform hard delete
            $reservation->forceDelete();

            Log::info('Hard deleted reservation ID: ' . $reservation->id);

            return [
                'error' => false,
                'message' => 'Reservation permanently deleted successfully',
            ];
        } catch (Exception $e) {
            // Log any errors
            Log::error('Error hard deleting reservation: ' . $e->getMessage());
            return ['error' => true, 'message' => 'An unexpected error occurred.'];
        }
    }
}