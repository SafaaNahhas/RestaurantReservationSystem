<?php

namespace App\Services\Emergency;

use App\Models\Emergency;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * EmergencyService
 *
 * This service provides methods to manage emergency records.
 * It encapsulates business logic for creating, updating, retrieving,
 * and deleting emergencies in the system.
 */
class EmergencyService
{
    /**
     * Store a new emergency record.
     *
     * @param array $data The data for the new emergency.
     * @return Emergency The created Emergency model instance.
     */
    public function storeEmergency(array $data)
    {
        try {
            return Emergency::create(array_filter($data));
        } catch (Exception $e) {
            Log::error("error while creating Emergency status. " . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status'  => 'error',
                'message' => 'Unexpected error while creating Emergency status'
            ], 500));
        }
    }

    /**
     * Update an existing emergency record.
     *
     * @param array $data The updated data for the emergency.
     * @param Emergency $emergency The existing Emergency model instance.
     * @return Emergency The updated Emergency model instance.
     */
    public function updateEmergency(array $data, $emergency_id)
    {
        try {
            $emergency = Emergency::findOrFail($emergency_id);
            $emergency->update(array_filter($data));
            return $emergency;
        } catch (ModelNotFoundException $e) {
            Log::error('Emergency status Not found. ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status'  => 'error',
                'message' => 'Emergency Not Found'
            ], 404));
        } catch (Exception $e) {
            Log::error('Unexpected error while updating Emergency. ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status'  => 'error',
                'message' => 'UnExcpected error while updating Emergency'
            ], 500));
        }
    }

    /**
     * Retrieve all emergency records.
     *
     * @return \Illuminate\Database\Eloquent\Collection A collection of all Emergency records.
     */
    public function listAllEmergencies()
    {
        try {
            return Emergency::all();
        } catch (Exception $e) {
            Log::error('Unexpected error while fetching all Emergency. ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status'  => 'error',
                'message' => 'UnExcpected error while fetching all Emergency'
            ], 500));
        }
    }

    /**
     * Delete an emergency record.
     *
     * @param int $emergency_id The Emergency model instance to delete.
     * @return void
     */
    public function deleteEmergency(int $emergency_id)
    {
        try {
            $emergency = Emergency::findOrFail($emergency_id);
            $emergency->delete();
        } catch (ModelNotFoundException $e) {
            Log::error('Emergency status Not found. ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status'  => 'error',
                'message' => 'Emergency Not Found'
            ], 404));
        } catch (Exception $e) {
            Log::error('Unexpected error while deleting Emergency. ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status'  => 'error',
                'message' => 'UnExcpected error while deleting Emergency'
            ], 500));
        }
    }

    /**
     * Retrieve a specific emergency record.
     *
     * @param int $emergency_id The Emergency model instance to retrieve.
     * @return Emergency The Emergency model instance.
     */
    public function showEmergecy(int $emergency_id)
    {
        try {
            $emergency = Emergency::findOrFail($emergency_id);
            return $emergency;
        } catch (ModelNotFoundException $e) {
            Log::error('Emergency status Not found. ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status'  => 'error',
                'message' => 'Emergency Not Found'
            ], 404));
        } catch (Exception $e) {
            Log::error('Unexpected error while fetching Emergency. ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status'  => 'error',
                'message' => 'UnExcpected error while fetching Emergency'
            ], 500));
        }
    }
}
