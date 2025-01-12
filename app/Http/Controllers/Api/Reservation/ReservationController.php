<?php

namespace App\Http\Controllers\Api\Reservation;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\Reservation\ReservationService;
use App\Http\Resources\Reservation\ReservationResource;
use Spatie\Permission\Exceptions\UnauthorizedException;
use PHPOpenSourceSaver\JWTAuth\Contracts\Providers\Auth;
use App\Http\Resources\Reservation\TableReservationResource;
use App\Http\Resources\Reservation\ShowTableReservationResource;
use App\Http\Requests\ReservationRequest\StoreReservationRequest;
use App\Http\Resources\Reservation\FaildTableReservationResource;
use App\Http\Requests\ReservationRequest\CancelReservationRequest;
use App\Http\Requests\ReservationRequest\UpdateReservationRequest;

class ReservationController extends Controller
{
     /**
     * reservation Service instance.
     *
     * @var ReservationService
     */
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
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Store a new reservation.
     *
     * @param StoreReservationRequest $request
     * @return JsonResponse
     */
    public function storeReservation(StoreReservationRequest $request): JsonResponse
    {
        if ($request->user()->cannot('store', Reservation::class)) {throw new UnauthorizedException(403);}
        $result = $this->reservationService->storeReservation($request->validated());
        // Handle the response based on the presence of reserved tables or reservation details
        return $result['status_code'] === 201
            ? self::success(new TableReservationResource($result['reservation']),  $result['message'], $result['status_code'])
            : self::error(isset($result['reserved_tables'])  ? FaildTableReservationResource::collection($result['reserved_tables'])    : null,   $result['message'], $result['status_code']);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
     /**
     * Update an existing reservation and return the response as JSON.
     *
     * @param UpdateReservationRequest $request The validated request object containing new reservation data.
     * @param int $id The ID of the reservation to update.
     * @return JsonResponse JSON response with status, message, and data.
     */
    public function updateReservation(UpdateReservationRequest $request, $id): JsonResponse
    {
        // Check if the user has permission to update the reservation
        if ($request->user()->cannot('update reservation', Reservation::class)) {throw new UnauthorizedException(403);}
        // Call the service to update the reservation
        $result = $this->reservationService->updateReservation($request->validated(), $id);
       // Return the response based on the result
        return $result['status_code'] === 200
            ? self::success(new TableReservationResource($result['reservation']), $result['message'], $result['status_code'])
            : self::error(isset($result['reserved_tables'])? FaildTableReservationResource::collection($result['reserved_tables']): null,$result['message'],$result['status_code']);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Get all tables with their reservations.
     *
     * @return JsonResponse
     */
    public function getAllTablesWithReservations(Request $request): JsonResponse
    {
        // if ($request->user()->cannot('getAllTablesWithReservations', Reservation::class)) {throw new UnauthorizedException(403);}
        $status = $request->input('status');
        $tables = $this->reservationService->getAllTablesWithReservations(['status' => $status,]);
        if ($tables->isEmpty()) {return self::error([], 'No tables found with the specified reservation status.', 200);}
        return self::success( ShowTableReservationResource::collection($tables), 'Tables with reservations retrieved successfully.',200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Confirm a reservation.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmReservation(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        if ($request->user()->cannot('confirm', $reservation)){throw new UnauthorizedException(403);}
        // Call the confirm reservation logic from the service
        $result = $this->reservationService->confirmReservation($id);
        if ($result['error']) {return self::error(null, $result['message'], 400);}
        return self::success($result['data'], $result['message'], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Reject a reservation.
     * @param Request $request The HTTP request object, containing the current user and their authorization.
     * @param int $reservationId The ID of the reservation to be rejected.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the result of the rejection attempt.
     *
     * @throws UnauthorizedException If the user does not have permission to reject the reservation.
     */
    public function rejectReservation(Request $request, $reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);
        if ($request->user()->cannot('reject',  $reservation)) {throw new UnauthorizedException(403);}
        $rejectionReason = $request->input('rejection_reason');
        // Call the service to handle the reservation rejection
        $result = $this->reservationService->rejectReservation($reservationId, $rejectionReason);
        // If there is an error, return a 400 response with the error message
        if ($result['error']) {return self::error(null, $result['message'], 400);}
        // Return the result with a success message and the reservation data
        return self::success($result['data'], $result['message'], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
    * Cancel a reservation.
     *
     * @param CancelReservationRequest $request
     * @param int $reservationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelReservation(CancelReservationRequest $request, $reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);
        // Fetch the reservation and check authorization
        if ($request->user()->cannot('cancel',   $reservation )) {throw new UnauthorizedException(403);}
        // Call the cancel logic from the service
        $result = $this->reservationService->cancelReservation($reservationId, $request->validated()['cancellation_reason']);
        if ($result['error']) {return self::error(null, $result['message'], 422);}
        return self::success($result['data'], $result['message'], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Start service for a reservation.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function startService(Request $request, $id)
    {
        if ($request->user()->cannot('startService', Reservation::class)) {throw new UnauthorizedException(403);}
        // Call the start service logic from the service
        $result = $this->reservationService->startService($id);
        if ($result['error']) {return self::error(null, $result['message'], 400);}
        return self::success($result['reservation'], 'Service started successfully', 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Complete service for a reservation.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeService(Request $request, $id)
    {
        if ($request->user()->cannot('completeService', Reservation::class)) {throw new UnauthorizedException(403);}
        // Call the complete service logic from the service
        $result = $this->reservationService->completeService($id);
        if ($result['error']) {return self::error(null, $result['message'], 400);}
        $reservation = $result['reservation'];
        if ($reservation->status === 'completed') {event(new \App\Events\ReservationCompleted($reservation));}
        return self::success($result['reservation'], 'Service completed successfully', 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Soft delete a reservation.
     *
     * @param Request $request The request object.
     * @param int $id The ID of the reservation to soft delete.
     * @return JsonResponse JSON response indicating success or failure.
     */
    public function softDeleteReservation(Request $request, $id)
    {
        if ($request->user()->cannot('soft delete reservation', Reservation::class))  {throw new UnauthorizedException(403);}
        $result = $this->reservationService->softDeleteReservation($id);
        if ($result['error']) {return self::error(null, $result['message'], 400);}
        return self::success(null, $result['message'], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Force delete a soft-deleted reservation.
     *
     * @param Request $request The request object.
     * @param int $id The ID of the reservation to force delete.
     * @return JsonResponse JSON response indicating success or failure.
     */
    public function forceDeleteReservation(Request $request, $id)
    {
        if (!$request->user()->can('hard delete reservation'))  {throw new UnauthorizedException(403);}
        $result = $this->reservationService->forceDeleteReservation($id);
        if ($result['error']) {return self::error(null, $result['message'], 400);}
        return self::success(null, $result['message'], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
    * Restore a soft-deleted reservation.
     *
     * @param Request $request The request object.
     * @param int $id The ID of the reservation to restore.
     * @return JsonResponse JSON response indicating success or failure.
     */
    public function restoreReservation(Request $request, $id)
    {
        if (!$request->user()->can('restorereservation')){throw new UnauthorizedException(403);}
        $result = $this->reservationService->restoreReservation($id);
        if ($result['error']) {return self::error(null, $result['message'], 400);}
        return self::success(null, $result['message'], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Retrieve all soft-deleted reservations.
     *
     * @param Request $request The request object.
     * @return JsonResponse JSON response containing soft-deleted reservations or an error message.
     */
    public function getSoftDeletedReservations(Request $request)
    {
        if (!$request->user()->can('view soft delete reservation')){throw new UnauthorizedException(403);}
        $result = $this->reservationService->getSoftDeletedReservations();
        if ($result['error']) {return self::error(null, $result['message'], 400);}
        return self::success($result['reservations'], 'Soft deleted reservations retrieved successfully', 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Get reservations by manager.
     *
     * @param Request $request
     * @param int $managerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReservationsByManager(Request $request, $managerId)
    {
        if (!$request->user()->can('viewReservationsByManager'))  {throw new UnauthorizedException(403);}
            // Fetch a sample reservation to check permissions
            $sampleReservation = Reservation::whereHas('table.department', function ($query) use ($managerId) {$query->where('manager_id', $managerId);})->firstOrFail();
            // Check permissions
            if (!$request->user()->can('viewReservationsByManager', $sampleReservation)) {throw new UnauthorizedException(403);}
            // Fetch reservations using the service
            $result = $this->reservationService->getReservationsByManager($managerId, $request->all());
            if ($result['error']) {return response()->json(['error' => true,'message' => $result['message'],], 400);}
            return response()->json(['error' => false,'reservations' => $result['reservations'],], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Get the most frequent user making reservations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMostFrequentUser(Request $request)
    {
        if (!$request->user()->can('viewMostFrequentUser'))  {throw new UnauthorizedException(403);}
        // Fetch the most frequent user
        $result = $this->reservationService->getMostFrequentUser();
        if ($result['error']) {return response()->json(['error' => true,'message' => $result['message'],], 400);}
        return response()->json(['error' => false,'most_frequent_user' => $result['most_frequent_user'],], 200);
    }
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Get reservations with 'in_service' status for a specific user.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getInServiceReservations()
    {


        // Retrieve the authenticated user's ID
        $userId = Auth()->id();

        // Fetch reservations with 'in_service' status for the given user ID
        $reservations = Reservation::getInServiceReservationsForUser($userId);

        // Return the reservations in the response
        return ReservationResource::collection($reservations);
    }

}
