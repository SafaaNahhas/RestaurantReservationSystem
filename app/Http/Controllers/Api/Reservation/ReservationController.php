<?php

namespace App\Http\Controllers\Api\Reservation;

use Carbon\Carbon;
use App\Models\Table;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use App\Services\ReservationService;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\TableReservationResource;
use App\Http\Resources\FaildTableReservationResource;
use App\Http\Requests\ReservationRequest\StoreReservationRequest;

class ReservationController extends Controller
{


    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }
    
    public function storeReservation(StoreReservationRequest $request): JsonResponse
    {
        $result = $this->reservationService->storeReservation($request->validated());

        if ($result['status_code'] !== 201) {
            $reservedTables = isset($result['reserved_tables']) ?
                $result['reserved_tables'] : null;

            if ($reservedTables instanceof LengthAwarePaginator) {
                return self::paginated(
                    $reservedTables,
                    FaildTableReservationResource::class,
                    $result['message'],
                    $result['status_code']
                );
            }

            return self::error(
                $reservedTables,
                $result['message'],
                $result['status_code']
            );
        }

        return self::success(
            new TableReservationResource($result['reservation']),
            $result['message'],
            $result['status_code']
        );
    }





        /**
         * Cancel unconfirmed reservations that are older than an hour.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function cancelUnconfirmedReservations()
        {
            try {
                // Call the cancel logic from the service
                $result = $this->reservationService->cancelUnconfirmedReservations();

                if ($result['error']) {
                    return self::error(null, $result['message'], 404);
                }

                return self::success($result['cancelled_reservations'], $result['message'], 200);
            } catch (\Exception $e) {
                // Log the error and return a generic message
                Log::error('Error canceling unconfirmed reservations: ' . $e->getMessage());
                return self::error(null, 'An unexpected error occurred.', 500);
            }
        }

        /**
         * Confirm a reservation.
         *
         * @param int $id
         * @return \Illuminate\Http\JsonResponse
         */
        public function confirmReservation($id)
        {
            try {
                // Call the confirm reservation logic from the service
                $result = $this->reservationService->confirmReservation($id);

                if ($result['error']) {
                    return self::error(null, $result['message'], 400);
                }

                return self::success($result['reservation'], 'Reservation confirmed successfully', 200);
            } catch (\Exception $e) {
                // Log the error and return a generic message
                Log::error('Error confirming reservation: ' . $e->getMessage());
                return self::error(null, 'An unexpected error occurred.', 500);
            }
        }

        /**
         * Cancel a reservation.
         *
         * @param int $reservationId
         * @return \Illuminate\Http\JsonResponse
         */
        public function cancelReservation($reservationId)
        {
            try {
                // Call the cancel logic from the service
                $result = $this->reservationService->cancelReservation($reservationId);

                if ($result['error']) {
                    return self::error(null, $result['message'], 422);
                }

                return self::success(null, $result['message'], 200);
            } catch (\Exception $e) {
                // Log the error and return a generic message
                Log::error('Error canceling reservation: ' . $e->getMessage());
                return self::error(null, 'An unexpected error occurred.', 500);
            }
        }

        /**
         * Start service for a reservation.
         *
         * @param int $id
         * @return \Illuminate\Http\JsonResponse
         */
        public function startService($id)
        {
            try {
                // Call the start service logic from the service
                $result = $this->reservationService->startService($id);

                if ($result['error']) {
                    return self::error(null, $result['message'], 400);
                }

                return self::success($result['reservation'], 'Service started successfully', 200);
            } catch (\Exception $e) {
                // Log the error and return a generic message
                Log::error('Error starting service: ' . $e->getMessage());
                return self::error(null, 'An unexpected error occurred.', 500);
            }
        }

        /**
         * Complete service for a reservation.
         *
         * @param int $id
         * @return \Illuminate\Http\JsonResponse
         */
        public function completeService($id)
        {
            try {
                // Call the complete service logic from the service
                $result = $this->reservationService->completeService($id);

                if ($result['error']) {
                    return self::error(null, $result['message'], 400);
                }

                return self::success($result['reservation'], 'Service completed successfully', 200);
            } catch (\Exception $e) {
                // Log the error and return a generic message
                Log::error('Error completing service: ' . $e->getMessage());
                return self::error(null, 'An unexpected error occurred.', 500);
            }
        }

        /**
         * Hard delete a reservation.
         *
         * @param int $id
         * @return \Illuminate\Http\JsonResponse
         */
        public function hardDeleteReservation($id)
        {
            try {
                // Call the hard delete logic from the service
                $result = $this->reservationService->hardDeleteReservation($id);

                if ($result['error']) {
                    return self::error(null, $result['message'], 422);
                }

                return self::success(null, $result['message'], 200);
            } catch (\Exception $e) {
                // Log the error and return a generic message
                Log::error('Error hard deleting reservation: ' . $e->getMessage());
                return self::error(null, 'An unexpected error occurred.', 500);
            }
        }













        }















