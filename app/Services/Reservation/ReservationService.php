<?php

namespace App\Services\Reservation;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Table;
use App\Models\Reservation;
use App\Jobs\SendRatingRequestJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Mail\ReservationConfirmMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationRejectedMail;
use Illuminate\Support\Facades\Cache;
use App\Mail\ReservationCancelledMail;
use App\Services\NotificationLogService;
use App\Jobs\NotifyManagersAboutReservation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReservationService
{
    protected $notificationLogService;
    public function __construct(NotificationLogService  $notificationLogService)
    {
        $this->notificationLogService = $notificationLogService;
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Store a new reservation.
     *
     * Handles validation, table availability, and reservation creation.
     * Returns an appropriate response for success or failure.
     *
     * @param array $data Reservation details (e.g., dates, table number, guest count).
     * @return array Response with status, message, and additional data if applicable.
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
            // Use findAvailableTable to find a suitable table
            $selectedTable = $this->findAvailableTable($data, $startDate, $endDate);

            // Handle table availability and department/manager validations
            if (!$selectedTable && Table::where('seat_count', '>=', $data['guest_count'])->doesntExist()) {
                return [
                    'status_code' => 422,
                    'message' => 'No tables are available to accommodate the required number of guests reserved. Consider booking multiple tables to accommodate your group.',
                ];
            }
            if (!$selectedTable) {
                $reservedTables = Table::whereHas('reservations')
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
            if (!$selectedTable) {
                $conflictingReservations = Reservation::where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($nestedQuery) use ($startDate, $endDate) {
                            $nestedQuery->where('start_date', '<', $startDate)
                                ->where('end_date', '>', $endDate);
                        });
                })
                ->whereIn('status', ['pending', 'reserved', 'in_service'])
                ->with('table')
                ->get();

                return [
                    'status_code' => 409,
                    'message' => isset($data['table_number'])
                        ? 'Selected table is not available for the selected time.'
                        : 'No available tables for the selected time.',
                    'conflicting_reservations' => $conflictingReservations,
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
                Log::warning('No table selected for reservation', ['start_date' => $startDate, 'end_date' => $endDate]);
                return [
                    'status_code' => 422,
                    'message' => 'The department does not have a manager.',
                ];
            }
            // Check if the authenticated user has filled in the notification settings
            $notificationSettings = auth()->user()->notificationSettings;

            if (!$notificationSettings) {
                return [
                    'status_code' => 422,
                    'message' => 'Notification settings must be configured before making a reservation.'
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
            // Clear relevant cache keys
            Cache::forget('tables_with_reservations_all');
            if (isset($data['status'])) {
                Cache::forget('tables_with_reservations_' . $data['status']);
            }
            // Notify managers
            NotifyManagersAboutReservation::dispatch($reservation, $this->notificationLogService);
            // Get the department and manager details
            $department = $selectedTable->department;
            $manager = $department->manager;
            // Log the department and manager information
            Log::info('Reservation made for table in department', [
                'department_name' => $department->name,
                'manager_name' => $manager->name,
                'manager_email' => $manager->email,
            ]);
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
                'message' => 'An unexpected error occurred.'. $e->getMessage(),
            ];
        }
    }
    ////////////////////////////////////////////////////////////////////////////////////////
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
                if (!$reservation) {
                    return [
                        'status_code' => 404,
                        'message' => 'Reservation not found.',
                    ];
                }
                // Ensure the reservation is in "pending" state before allowing updates
                if ($reservation->status !== 'pending') {
                    return [
                        'status_code' => 422,
                        'message' => 'Reservation can only be updated if its status is "pending".',
                    ];
                }
                $startDate = isset($data['start_date']) && !empty($data['start_date'])
                    ? Carbon::parse($data['start_date'])
                    : Carbon::parse($reservation->start_date);
                $endDate = isset($data['end_date']) && !empty($data['end_date'])
                    ? Carbon::parse($data['end_date'])
                    : Carbon::parse($reservation->end_date);
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
                // Step 4: Ensure guest_count is set or use the reservation value
                $guestCount = $data['guest_count'] ?? $reservation->guest_count;
                // Handle table availability
                $selectedTable = $this->AvailableTable($data, $startDate,
                $endDate, $reservationId);
                if (!$selectedTable) {
                    $reservedTables = Table::whereHas('reservations')
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
                if ($selectedTable && $selectedTable->seat_count < $guestCount) {
                    return [
                        'status_code' => 422,
                        'message' => 'The selected table does not have enough seats for the number of guests.',
                    ];
                }
                if (!$selectedTable && Table::where('seat_count', '>=', $guestCount)->doesntExist()) {
                    return [
                        'status_code' => 422,
                        'message' => 'No tables are available to accommodate the required number of guests reserved. Consider booking multiple tables to accommodate your group.',
                    ];
                }
                if (!$selectedTable->department) {
                    return [
                        'status_code' => 422,
                        'message' => 'The table does not belong to any department.',
                    ];
                }

                if (!$selectedTable->department->manager) {
                    Log::warning('No manager found for department.', ['start_date' => $startDate, 'end_date' => $endDate]);
                    return [
                        'status_code' => 422,
                        'message' => 'The department does not have a manager.',
                    ];
                }

                // Update reservation details
                $reservation->update([
                    'table_id' => $selectedTable->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'guest_count' => $guestCount,
                    'services' => $data['services'] ?? $reservation->services,
                ]);


                Cache::forget('tables_with_reservations_all');
                if (isset($data['status'])) {
                    Cache::forget('tables_with_reservations_' . $data['status']);
                }

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

    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Find an available table for the specified reservation details.
     *
     * @param array $data Reservation data including table number and guest count.
     * @param Carbon $startDate Start date and time of the reservation.
     * @param Carbon $endDate End date and time of the reservation.
     * @param int|null $excludeReservationId Reservation ID to exclude from conflict checks.
     * @return Table|null The available table or null if none found.
     */
    public function findAvailableTable(array $data, $startDate, $endDate, $excludeReservationId = null)
    {
        return Table::when(isset($data['table_number']), function ($query) use ($data) {
            return $query->where('table_number', $data['table_number']);
        }, function ($query) use ($data) {
            return $query->where('seat_count', '>=', $data['guest_count'])
                ->orderBy('seat_count', 'asc');
        })
            ->whereDoesntHave('reservations', function ($query) use ($startDate, $endDate, $excludeReservationId) {
                $query->where(function ($reservationQuery) use ($startDate, $endDate) {
                    $reservationQuery->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($nestedQuery) use ($startDate, $endDate) {
                            $nestedQuery->where('start_date', '<', $startDate)
                                ->where('end_date', '>', $endDate);
                        });
                })
                ->when($excludeReservationId, function ($query) use ($excludeReservationId) {
                    $query->where('id', '!=', $excludeReservationId);
                })
                ->whereIn('status', ['pending', 'reserved', 'in_service']);
            })
            ->select(['id', 'table_number', 'seat_count', 'department_id'])
            ->first();
    }

////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Check for an available table that meets the reservation criteria.
     *
     * @param array $data Reservation data including table number and guest count.
     * @param Carbon $startDate Start date and time of the reservation.
     * @param Carbon $endDate End date and time of the reservation.
     * @param int|null $excludeReservationId Reservation ID to exclude from conflict checks.
     * @return Table|null The available table or null if none found.
     */
    public function AvailableTable(array $data, $startDate, $endDate, $excludeReservationId = null)
    {
        return Table::when(isset($data['table_number']), function ($query) use ($data) {
            return $query->where('table_number', $data['table_number']);
        }, function ($query) use ($data) {
            return $query->where('seat_count', '>=', 'guest_count')
                ->orderBy('seat_count', 'asc');
        })
            ->whereDoesntHave('reservations', function ($query) use ($startDate, $endDate, $excludeReservationId) {
                $query->where(function ($reservationQuery) use ($startDate, $endDate) {
                    $reservationQuery->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($nestedQuery) use ($startDate, $endDate) {
                            $nestedQuery->where('start_date', '<', $startDate)
                                ->where('end_date', '>', $endDate);
                        });
                })
                ->when($excludeReservationId, function ($query) use ($excludeReservationId) {
                    $query->where('id', '!=', $excludeReservationId);
                })
                ->whereIn('status', ['pending', 'reserved', 'in_service']);
            })
            ->select(['id', 'table_number', 'seat_count', 'department_id'])
            ->first();
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Get all tables with their reservations.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTablesWithReservations(array $filter = [], User $user)
    {
        try {
            $cacheKey = 'tables_with_reservations_' . md5(json_encode($filter) . $user->id);
            $cacheTTL = 600;

            return Cache::remember($cacheKey, $cacheTTL, function () use ($filter, $user) {
                $query = Table::query();

                if ($user->hasRole('manager')) {
                    $query->whereHas('department', function ($departmentQuery) use ($user) {
                        $departmentQuery->where('manager_id', $user->id);
                    });
                }

                if ($user->hasRole('customer')) {
                    $query->whereHas('reservations', function ($reservationQuery) {
                        $reservationQuery->whereIn('status', ['cancelled', 'completed']);
                    });
                }

                $query->whereHas('reservations', function ($reservationQuery) use ($filter) {
                    if (isset($filter['status'])) {
                        $reservationQuery->where('status', $filter['status']);
                    }
                });

                return $query->with(['reservations' => function ($reservationQuery) use ($filter) {
                    if (isset($filter['status'])) {
                        $reservationQuery->where('status', $filter['status']);
                    }
                }])->get();
            });
        } catch (Exception $e) {
            Log::error('Error fetching tables with reservations: ' . $e->getMessage());
            return collect([]);
        }
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Service method to confirm a reservation.
     *
     * @param int $reservationId
     * @return array
     */
    public function confirmReservation($reservationId): array
    {
        try {
            // Find the reservation or throw an exception if not found
            $reservation = Reservation::with('user', 'table', 'user.notificationSettings:id,user_id,method_send_notification,telegram_chat_id,send_notification_options')
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
            // Update the reservation status to 'confirmed'
            $reservation->update(['status' => 'confirmed']);
            // Clear cache for all reservations
            Cache::forget('tables_with_reservations_all');
            Cache::forget('tables_with_reservations_' . md5(json_encode(['status' => 'pending'])));
            Log::info("Reservation ID {$reservation->id} confirmed by User ID {$reservation->user_id}.");
            $notificationSettings = $reservation->user->notificationSettings;
                $botToken = env('TELEGRAM_BOT_TOKEN');
                $chatId = $notificationSettings->telegram_chat_id;
                $message = "✅ Reservation Confirmed!\n\n";
                $message .= "📅 Date: " . $reservation->start_date . "\n"; // Dynamic date formatting
                $message .= "🍽️ Table Number: " . ($reservation->table->table_number ?? 'Not Available') . "\n\n"; // Check if table number is available
                $message .= "Thank you for choosing our service! ❤️";
                // Send notification based on the user's selected method
                if ($notificationSettings->method_send_notification === 'telegram' && $chatId) {
                    // Send Telegram message
                    Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => $message,
                    ]);
                } elseif ($notificationSettings->method_send_notification === 'mail') {
                    // Send confirmation email
                    Mail::to($reservation->user->email)
                        ->queue(new ReservationConfirmMail($reservation));
                }
                 else {
                    return [
                        'error' => true,
                        'message' => 'Invalid notification method in settings.',
                    ];
                }

            // Return response with notification status included
            return [
                'error' => false,
                'message' => 'Reservation confirmed successfully with sent confirmation notifications',
                'data' => [
                    'reservation_id' => $reservation->id,
                    'table_number' => $reservation->table->table_number,
                    'start_date' => $reservation->start_date,
                    'end_date' => $reservation->end_date,
                ],
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'error' => true,
                'message' => "No reservation found with ID {$reservationId}",
                'data' => null,
            ];
        } catch (Exception $e) {
            Log::error('Error confirming reservation: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'An unexpected error occurred.' . $e->getMessage(),
            ];
        }
    }
    ////////////////////////////////////////////////////////////////////////////////////////
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
            $reservation = Reservation::with('user', 'table', 'user.notificationSettings:id,user_id,method_send_notification,telegram_chat_id,send_notification_options')
                ->findOrFail($reservationId);

            if ($reservation->status !== 'pending') {
                return [
                    'error' => true,
                    'message' => 'Reservation must be in pending state to reject',
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
            // Clear cache for all reservations
            Cache::forget('tables_with_reservations_all');
            Cache::forget('tables_with_reservations_' . md5(json_encode(['status' => 'pending'])));
            Log::info("Reservation ID {$reservation->id} rejected by User ID {$reservation->user_id}. Reason: {$rejectionReason}");
            $notificationSettings = $reservation->user->notificationSettings;
            $botToken = env('TELEGRAM_BOT_TOKEN');
            $chatId = $notificationSettings->telegram_chat_id;
            $message = "❌ Reservation Rejected!\n\n";
            $message .= "📅 Date: " . $reservation->start_date . "\n";
            $message .= "🍽️ Table Number: " . ($reservation->table->table_number ?? 'Not Available') . "\n";
            $message .= "⚠️ Rejection Reason: " . $rejectionReason . "\n\n";
            $message .= "We hope to see you again. Thank you for your understanding. 🙏";
            // Send notification based on the user's selected method
            if ($notificationSettings->method_send_notification === 'telegram' && $chatId) {
                // Send Telegram message
                Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                ]);
            } elseif ($notificationSettings->method_send_notification === 'mail') {
                // Send rejection email
                Mail::to($reservation->user->email)
                    ->queue(new ReservationRejectedMail($reservation));
            } else {
                return [
                    'error' => true,
                    'message' => 'Invalid notification method in settings.',
                ];
            }
            // Return response with notification status included
            return [
                'error' => false,
                'message' => 'Reservation rejected successfully With Send  rejection notifications',
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
                'message' => 'An unexpected error occurred.' . $e->getMessage(),
            ];
        }
    }
    ////////////////////////////////////////////////////////////////////////////////////////
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

            if ($reservation->status !== 'confirmed') {
                return [
                    'error' => true,
                    'message' => 'Reservation must be in confirm state to cancel',
                ];
            }

            if (Carbon::parse($reservation->start_date)->isPast()) {
                return [
                    'error' => true,
                    'message' => 'Cannot modify past reservations',
                ];
            }

            // Update the reservation status to 'cancelled'
            $reservation->update(['status' => 'cancelled']);

            // Create a detail record for the cancellation
            $reservation->details()->create([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $cancellationReason,
            ]);
            // Clear cache for all reservations
            Cache::forget('tables_with_reservations_all');
            Cache::forget('tables_with_reservations_' . md5(json_encode(['status' => 'pending'])));
            // Log the cancellation
            Log::info("Reservation ID {$reservation->id} cancelled by User ID {$reservation->user_id}. Reason: {$cancellationReason}");
            // Send cancellation email to the manager (if any)
            $manager = $reservation->table->department->manager;
            if ($manager) {
                Mail::to($manager->email)
                    ->queue(new ReservationCancelledMail($reservation, true)); // Pass `true` for the manager
            }
            // Send cancellation notification to the user if they opted for it
            $notificationSettings = $reservation->user->notificationSettings;

            $botToken = env('TELEGRAM_BOT_TOKEN');
            $chatId = $notificationSettings->telegram_chat_id;
            $message = "❌ Reservation Cancelled!\n\n";
            $message .= "📅 Date: " . $reservation->start_date . "\n";
            $message .= "🍽️ Table Number: " . ($reservation->table->table_number ?? 'Not Available') . "\n";
            $message .= "⚠️ Cancellation Reason: " . $cancellationReason . "\n\n";
            $message .= "We hope to see you again. Thank you for your understanding. 🙏";

                // Send notification based on the user's selected method
                if ($notificationSettings->method_send_notification === 'telegram' && $chatId) {
                    // Send Telegram message
                    Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => $message,
                    ]);
                } elseif ($notificationSettings->method_send_notification === 'mail') {
                    // Send cancellation email
                    Mail::to($reservation->user->email)
                        ->queue(new ReservationCancelledMail($reservation, false));
                } else {
                    return [
                        'error' => true,
                        'message' => 'Invalid notification method in settings.',
                    ];
                }
            // Send cancellation email to the manager (if any)
            $manager = $reservation->table->department->manager;
            if ($manager) {
                Mail::to($manager->email)
                    ->queue(new ReservationCancelledMail($reservation, true)); // Pass `true` for the manager
            }

            return [
                'error' => false,
                'message' => 'Reservation cancelled successfully with sent cancellation notifications',
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

    ////////////////////////////////////////////////////////////////////////////////////////
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
            // Clear cache for all reservations
            Cache::forget('tables_with_reservations_all');
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
    ////////////////////////////////////////////////////////////////////////////////////////
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
        // Clear cache for related reservation data
        if ($reservation->status === 'pending') {
            Cache::forget('tables_with_reservations_pending');
        }
        Cache::forget('tables_with_reservations_all');

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
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Soft delete a reservation based on specific conditions.
     *
     * @param int $reservationId The ID of the reservation to soft delete.
     * @return array Response indicating success or failure.
     */
    public function softDeleteReservation($reservationId)
    {
        try {
            $user = auth()->user();
            $reservation = Reservation::with('table.department.manager')->findOrFail($reservationId);
            if ($user->hasRole('Customer')) {
                if ($reservation->user_id !== $user->id) {
                    return [
                        'error' => true,
                        'message' => 'You are not authorized to delete this reservation.',
                    ];
                }
                if ($reservation->status !== 'pending') {
                    return [
                        'error' => true,
                        'message' => 'You can only delete reservations with a pending status.',
                    ];
                }
            }
            if ($user->hasRole('Manager')) {
                if (!$reservation->table || $reservation->manager_id != $user->id) {
                    return [
                        'error' => true,
                        'message' => 'You can only delete reservations within your department.',
                    ];
                }
                if (!in_array($reservation->status, ['completed', 'rejected', 'cancelled'])) {
                    return [
                        'error' => true,
                        'message' => 'You can only delete reservations with statuses completed, rejected, or cancelled.',
                    ];
                }
            }
            if ($user->hasRole('Admin')) {
                if (!in_array($reservation->status, ['completed', 'rejected', 'cancelled'])) {
                    return [
                        'error' => true,
                        'message' => 'You can only delete reservations with statuses completed, rejected, or cancelled.',
                    ];
                }
            }
            $reservation->delete();
            Log::info("Reservation with ID {$reservationId} has been soft deleted by user {$user->id}.");
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
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Permanently delete a soft-deleted reservation.
     *
     * @param int $reservationId The ID of the reservation to force delete.
     * @return array Response indicating success or failure.
     */
    public function forceDeleteReservation($reservationId)
    {
        try {
            $user = auth()->user();
            $reservation = Reservation::withTrashed()->with('table.department.manager')->findOrFail($reservationId);
            if (!$reservation->trashed()) {
                return [
                    'error' => true,
                    'message' => 'Force delete is only allowed for soft-deleted reservations.',
                ];
            }
            if ($user->hasRole('Manager')) {
                if (!$reservation->table || $reservation->manager_id != $user->id) {
                    return [
                        'error' => true,
                        'message' => 'You can only force delete reservations assigned to you.',
                    ];
                }
            }
            $reservation->forceDelete();
            Cache::forget('tables_with_reservations_all');
            Cache::forget('tables_with_reservations_' . md5(json_encode(['status' => 'pending'])));
            Log::info("Reservation with ID {$reservationId} has been force deleted by user {$user->id}.");
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
            return [
                'error' => true,
                'message' => 'An unexpected error occurred.',
            ];
        }
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Restore a soft-deleted reservation.
     *
     * @param int $reservationId The ID of the reservation to restore.
     * @return array Response indicating success or failure.
     */
    public function restoreReservation($reservationId)
    {
        try {
            $user = auth()->user();
            $reservation = Reservation::onlyTrashed()->with('table.department.manager')->findOrFail($reservationId);
            if ($user->hasRole('manager')) {
                $managerDepartment = $user->department;
                if (!$managerDepartment || $reservation->table->department->id !== $managerDepartment->id) {
                    return [
                        'error' => true,
                        'message' => 'You can only restore reservations within your department.',
                    ];
                }
            }
            $table = $reservation->table;
            $startDate = Carbon::parse($reservation->start_date);
            $endDate = Carbon::parse($reservation->end_date);
            $conflictingReservation = Reservation::where('table_id', $table->id)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($nestedQuery) use ($startDate, $endDate) {
                            $nestedQuery->where('start_date', '<', $startDate)
                                ->where('end_date', '>', $endDate);
                        });
                })
                ->whereNotIn('status', ['cancelled', 'completed']) // Exclude cancelled and completed
                ->exists();
            if ($conflictingReservation) {
                return [
                    'error' => true,
                    'message' => 'Conflict found with another reservation for the selected table and date range.',
                ];
            }
            $reservation->restore();
            Cache::forget('tables_with_reservations_all');
            Cache::forget('tables_with_reservations_' . md5(json_encode(['status' => 'pending'])));
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
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Retrieve all soft-deleted reservations.
     *
     * @return array Response containing soft-deleted reservations or an error message.
     */
    public function getSoftDeletedReservations()
    {
        try {
            $user = auth()->user();
            if ($user->hasRole('manager')) {
                $managerDepartment = $user->department;
                if (!$managerDepartment) {
                    return [
                        'error' => true,
                        'message' => 'You do not have a department assigned to manage.',
                    ];
                }
                $reservations = Reservation::onlyTrashed()
                    ->whereHas('table.department', function ($query) use ($managerDepartment) {
                        $query->where('id', $managerDepartment->id);
                    })
                    ->get();
            } else {
                $reservations = Reservation::onlyTrashed()->get();
            }

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
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Get reservations by manager with optional filtering.
     *
     * @param int $managerId
     * @param array $filter
     * @return array
     */
    public function getReservationsByManager($managerId, array $filter = [])
    {
        try {
            Cache::forget('tables_with_reservations_all');
            if (isset($filter['status'])) {
                Cache::forget('tables_with_reservations_' . md5(json_encode(['status' => $filter['status']])));
            }
            $reservations = Reservation::whereHas('table.department', function ($query) use ($managerId) {
                $query->where('manager_id', $managerId);
            })
                ->when(isset($filter['status']), function ($query) use ($filter) {
                    $query->whereIn('status', (array)$filter['status']);
                })
                ->with(['table:id,table_number', 'user:id,name,email'])
                ->get();
            return [
                'error' => false,
                'reservations' => $reservations,
            ];
        } catch (Exception $e) {
            Log::error('Error fetching reservations by manager: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'An unexpected error occurred.',
            ];
        }
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Get the most frequent user making reservations.
     *
     * @return array
     */
    public function getMostFrequentUser()
    {
        try {
            Cache::forget('tables_with_reservations_all');
            Cache::forget('tables_with_reservations_' . md5(json_encode(['status' => 'pending'])));

            $user = Reservation::select('user_id', DB::raw('COUNT(*) as reservation_count'))
                ->groupBy('user_id')
                ->orderByDesc('reservation_count')
                ->with(['user' => function ($query) {
                    $query->select('id', 'name', 'email');
                }])
                ->first();
            if (!$user) {
                return [
                    'error' => true,
                    'message' => 'No reservations found.',
                ];
            }
            return [
                'error' => false,
                'most_frequent_user' => $user,
            ];
        } catch (Exception $e) {
            Log::error('Error fetching most frequent user: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'An unexpected error occurred. ' . $e->getMessage(),
            ];
        }
    }
}
