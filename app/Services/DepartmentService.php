<?php

namespace App\Services;

use Exception;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DepartmentService - Provides functionality for managing Department.
 *
 * This service class handles the business logic for managing Department, including
 * fetching, creating, updating, and deleting Department. It includes error handling
 * and logs issues that arise during these operations.
 */
class DepartmentService
{

   

    /**
     * Retrieve all departments with pagination.
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllDepartments()
    {
        try {
            return Department::paginate(10);
        } catch (Exception $e) {
            Log::error('Error fetching departments: ' . $e->getMessage());
            throw new \RuntimeException('Unable to fetch departments.');
        }
    }

    /**
     * Create a new department.
     *
     * @param array $data
     * @return Department
     */
    public function createDepartment(array $data)
    {
        try {
            return Department::create($data);
        } catch (Exception $e) {
            Log::error('Error creating department: ' . $e->getMessage());
            throw new \RuntimeException('Unable to create department.');
        }
    }

    /**
     * Update an existing department.
     *
     * @param Department $department
     * @param array $data
     * @return Department
     */

    public function updateDepartment(Department $department, array $data)
    {
        try {
            $department->update($data);
            return $department;
        } catch (Exception $e) {
            Log::error('Error updating department (ID: ' . $department->id . '): ' . $e->getMessage());
            throw new \RuntimeException('Unable to update department.');
        }
    }

    /**
     * Delete a department.
     *
     * @param Department $department
     * @return bool
     */
    public function deleteDepartment(Department $department)
    {
        try {
            $department->delete();
        } catch (Exception $e) {
            Log::error('Error deleting department (ID: ' . $department->id . '): ' . $e->getMessage());
            throw new \RuntimeException('Unable to delete department.');
        }
    }

    /**
     * Get all soft-deleted departments.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDeletedDepartments()
    {
        return Department::onlyTrashed()->get();
    }

    /**
     * Restore a soft-deleted department by ID.
     *
     * @param string $id
     * @return Department
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function restoreDeletedDepartment(string $id): Department
    {
        $department = Department::onlyTrashed()->findOrFail($id);
        $department->restore();
        return $department;
    }

    /**
     * Permanently delete a soft-deleted department by ID.
     *
     * @param string $id
     * @return void
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function permanentlyDeleteDepartment(string $id): void
    {
        $department = Department::onlyTrashed()->findOrFail($id);
        $department->forceDelete();
    }
}

