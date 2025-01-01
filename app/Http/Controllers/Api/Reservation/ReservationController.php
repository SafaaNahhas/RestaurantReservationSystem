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
use App\Http\Resources\ShowTableReservationResource;
use App\Http\Resources\FaildTableReservationResource;
use App\Http\Requests\ReservationRequest\StoreReservationRequest;
use Spatie\Permission\Exceptions\UnauthorizedException;

class ReservationController extends Controller
{


    protected $reservationService;
    /**
     * ReservationController constructor.
     *
     * @param ReservationService $reservationService
     */
    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }
    /**
     * Store a new reservation.
     *
     * @param StoreReservationRequest $request
     * @return JsonResponse
     */

    public function storeReservation(StoreReservationRequest $request): JsonResponse
    {
        if ($request->user()->cannot('store', Reservation::class)) {
            throw new UnauthorizedException(403);
        }
        $result = $this->reservationService->storeReservation($request->validated());
        // Handle the response based on the presence of reserved tables or reservation details
        return $result['status_code'] === 201
            ? self::success(new TableReservationResource($result['reservation']),  $result['message'], $result['status_code'])
            : self::error(isset($result['reserved_tables'])  ? FaildTableReservationResource::collection($result['reserved_tables'])    : null,   $result['message'], $result['status_code']);
    }

    /**
     * Get all tables with their reservations.
     *
     * @return JsonResponse
     */
    public function getAllTablesWithReservations(): JsonResponse
    {
        // Fetch tables with reservations using the service
        $tables = $this->reservationService->getAllTablesWithReservations();
        // Use the resource collection to format the response
        return self::success(ShowTableReservationResource::collection($tables), 'Tables with reservations retrieved successfully.', 200);
    }

    /**
     * Cancel unconfirmed reservations that are older than an hour.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelUnconfirmedReservations(Request $request)
    {
        if ($request->user()->cannot('cancelUnConfirmed', Reservation::class)) {
            throw new UnauthorizedException(403);
        }
        // Call the cancel logic from the service
        $result = $this->reservationService->cancelUnconfirmedReservations();

        if ($result['error']) {
            return self::error(null, $result['message'], 404);
        }

        return self::success($result['cancelled_reservations'], $result['message'], 200);
    }

    /**
     * Confirm a reservation.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmReservation(Request $request, $id)
    {

        if ($request->user()->cannot('confirm', Reservation::class)) {
            throw new UnauthorizedException(403);
        }
        // Call the confirm reservation logic from the service
        $result = $this->reservationService->confirmReservation($id);

        if ($result['error']) {
            return self::error(null, $result['message'], 400);
        }

        return self::success($result['reservation'], 'Reservation confirmed successfully', 200);
    }

    /**
     * Cancel a reservation.
     *
     * @param int $reservationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelReservation(Request $request, $reservationId)
    {

        $reservation = Reservation::findOrFail($reservationId);
        if ($request->user()->cannot('cancel', $reservation)) {
            throw new UnauthorizedException(403);
        }
        // Call the cancel logic from the service
        $result = $this->reservationService->cancelReservation($reservationId);

        if ($result['error']) {
            return self::error(null, $result['message'], 422);
        }

        return self::success(null, $result['message'], 200);
    }

    /**
     * Start service for a reservation.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function startService(Request $request, $id)
    {

        if ($request->user()->cannot('startService', Reservation::class)) {
            throw new UnauthorizedException(403);
        }
        // Call the start service logic from the service
        $result = $this->reservationService->startService($id);

        if ($result['error']) {
            return self::error(null, $result['message'], 400);
        }

        return self::success($result['reservation'], 'Service started successfully', 200);
    }

    /**
     * Complete service for a reservation.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeService(Request $request, $id)
    {

        if ($request->user()->cannot('completeService', Reservation::class)) {
            throw new UnauthorizedException(403);
        }
        // Call the complete service logic from the service
        $result = $this->reservationService->completeService($id);

        if ($result['error']) {
            return self::error(null, $result['message'], 400);
        }
        $reservation = $result['reservation'];

        // التحقق من الحالة وإطلاق الإيفنت
        if ($reservation->status === 'completed') { // الشرط يتحقق إذا اكتملت الخدمة
            event(new \App\Events\ReservationCompleted($reservation));
        }

        return self::success($result['reservation'], 'Service completed successfully', 200);
    }

    /**
     * Hard delete a reservation.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function hardDeleteReservation(Request $request, $id)
    {

        if ($request->user()->cannot('delete', Reservation::class)) {
            throw new UnauthorizedException(403);
        }
        // Call the hard delete logic from the service
        $result = $this->reservationService->hardDeleteReservation($id);

        if ($result['error']) {
            return self::error(null, $result['message'], 422);
        }

        return self::success(null, $result['message'], 200);
    }
}
