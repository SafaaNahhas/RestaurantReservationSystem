<?php

namespace App\Services;

use App\Models\Emergency;

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
        return Emergency::create(array_filter($data));
    }

    /**
     * Update an existing emergency record.
     *
     * @param array $data The updated data for the emergency.
     * @param Emergency $emergency The existing Emergency model instance.
     * @return Emergency The updated Emergency model instance.
     */
    public function updateEmergency(array $data, Emergency $emergency)
    {
        $emergency->update(array_filter($data));
        return $emergency;
    }

    /**
     * Retrieve all emergency records.
     *
     * @return \Illuminate\Database\Eloquent\Collection A collection of all Emergency records.
     */
    public function listAllEmergencies()
    {
        return Emergency::all();
    }

    /**
     * Delete an emergency record.
     *
     * @param Emergency $emergency The Emergency model instance to delete.
     * @return void
     */
    public function deleteEmergency(Emergency $emergency)
    {
        $emergency->delete();
    }

    /**
     * Retrieve a specific emergency record.
     *
     * @param Emergency $emergency The Emergency model instance to retrieve.
     * @return Emergency The Emergency model instance.
     */
    public function showEmergecy(Emergency $emergency)
    {
        return $emergency;
    }
}
