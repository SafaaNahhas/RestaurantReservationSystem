<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Table;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Mail\ReservationDetailsMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationCancelledMail;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\TableReservationResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReservationService
{



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

            if ($startDate->diffInHours($endDate) > 6 || !$startDate->isSameDay($endDate)) {
                return [
                    'status_code' => 422,
                    'message' => 'Reservations must not exceed 6 hours and must be within the same day.'
                ];
            }

                $selectedTable = Table::when(isset($data['table_number']), function ($query) use ($data) {
                    return $query->where('table_number', $data['table_number']);
                }, function ($query) use ($data) {
                    return $query->where('seat_count', '>=', $data['guest_count']);
                })
                    ->whereDoesntHave('reservations', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function ($nestedQuery) use ($startDate, $endDate) {
                                $nestedQuery->where('start_date', '<', $startDate)
                                            ->where('end_date', '>', $endDate);
                            });
                    })
                    ->select(['id', 'table_number', 'seat_count'])
                    ->first();
                    if (!$selectedTable && Table::where('seat_count', '>=', $data['guest_count'])->doesntExist()) {
                        return [
                            'status_code' => 422,
                            'message' => 'No tables are available to accommodate the required number of guests reserved. Consider booking multiple tables to accommodate your group.',
                        ];}
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

                $reservation = Reservation::create([
                    'user_id' => auth()->id(),
                    'table_id' => $selectedTable->id,
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'guest_count' => $data['guest_count'],
                    'services' => $data['services'] ?? null,
                    'status' => 'pending',
                ]);

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
         * Get all tables with their reservations.
         *
         * @return \Illuminate\Database\Eloquent\Collection
         */
        public function getAllTablesWithReservations()
        {
            return Table::with(['reservations' => function ($query) {
                $query->select('id', 'table_id', 'start_date', 'end_date', 'status');
            }])->get();
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
    public function cancelReservation($reservationId): array
    {
        try {
            // Find the reservation or throw an exception if not found
            $reservation = Reservation::with('user', 'table')->findOrFail($reservationId);

            if ($reservation->status === 'cancelled') {
                return [
                    'error' => true,
                    'message' => 'The reservation is already cancelled.',
                ];
            }

            // Update the reservation status to 'cancelled'
            $reservation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Log the cancellation
            Log::info("Reservation ID {$reservation->id} cancelled by User ID {$reservation->user_id}");

            // Send cancellation email (queue for async processing)
            Mail::to($reservation->user->email)
                ->queue(new ReservationCancelledMail($reservation));

            return [
                'error' => false,
                'message' => 'Reservation cancelled successfully',
                'data' => [
                    'reservation_id' => $reservation->id,
                    'table_number' => $reservation->table->table_number,
                    'start_date' => $reservation->start_date,
                    'end_date' => $reservation->end_date,
                ],
            ];
        } catch (ModelNotFoundException $e) {
            Log::warning("Reservation with ID {$reservationId} not found.");
            return [
                'error' => true,
                'message' => "No reservation found with ID {$reservationId}",
            ];
        } catch (Exception $e) {
            Log::error('Error canceling reservation: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'An unexpected error occurred.',
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
