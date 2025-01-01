<?php

namespace App\Http\Controllers\Api\Reservation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emergency\StoreEmergencyRequest;
use App\Http\Requests\Emergency\UpdateEmergencyRequest;
use App\Models\Emergency;
use App\Services\EmergencyService;

/**
 * EmergencyController
 *
 * This controller handles API requests for managing emergency records.
 * It uses the EmergencyService to perform the underlying business logic.
 */
class EmergencyController extends Controller
{
    /**
     * @var EmergencyService
     * The service instance used for emergency-related operations.
     */
    protected $emergencyService;

    /**
     * Constructor
     *
     * @param EmergencyService $emergencyService The service for emergency operations.
     */
    public function __construct(EmergencyService $emergencyService)
    {
        $this->emergencyService = $emergencyService;
    }

    /**
     * List all emergencies.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response with all emergency records.
     */
    public function index()
    {
        $results = $this->emergencyService->listAllEmergencies();
        return self::success(
            $results,
            'Emergencies Retrieved Successfully',
            200
        );
    }

    /**
     * Store a new emergency.
     *
     * @param StoreEmergencyRequest $request The validated request data.
     * @return \Illuminate\Http\JsonResponse A JSON response with the created emergency record.
     */
    public function store(StoreEmergencyRequest $request)
    {
        $result = $this->emergencyService->storeEmergency($request->validated());
        return self::success(
            $result,
            'Emergency Created Successfully',
            201
        );
    }

    /**
     * Update an existing emergency.
     *
     * @param UpdateEmergencyRequest $request The validated request data.
     * @param Emergency $emergency The emergency to be updated.
     * @return \Illuminate\Http\JsonResponse A JSON response with the updated emergency record.
     */
    public function update(UpdateEmergencyRequest $request, Emergency $emergency)
    {
        $result = $this->emergencyService->updateEmergency($request->validated(), $emergency);
        return self::success(
            $result,
            'Emergency Updated Successfully',
            200
        );
    }

    /**
     * Show a specific emergency.
     *
     * @param Emergency $emergency The emergency to retrieve.
     * @return \Illuminate\Http\JsonResponse A JSON response with the emergency record.
     */
    public function show(Emergency $emergency)
    {
        $result = $this->emergencyService->showEmergecy($emergency);
        return self::success(
            $result,
            'Emergency Retrieved Successfully',
            200
        );
    }

    /**
     * Delete an emergency.
     *
     * @param Emergency $emergency The emergency to delete.
     * @return \Illuminate\Http\JsonResponse A JSON response confirming deletion.
     */
    public function destroy(Emergency $emergency)
    {
        $this->emergencyService->deleteEmergency($emergency);
        return self::success(
            null,
            'Emergency Deleted Successfully',
            200
        );
    }
}
