<?php

namespace App\Http\Controllers\Api\Reservation;

use Log;
use Exception;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use App\Services\DepartmentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;

class DepartmentController extends Controller
{
    protected $departmentService;

    /**
     * DepartmentController constructor.
     *
     * @param DepartmentService $departmentService
     */
    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    /**
     * Display a listing of departments.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $departments = $this->departmentService->getAllDepartments(); 
    
        return self::paginated($departments, DepartmentResource::class, 'departments retrieved successfully.', 200);
    }
    

    /**
     * Store a newly created department in storage.
     *
     * @param StoreDepartmentRequest $request
     * @return JsonResponse
     */
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->departmentService->createDepartment($request->validated());

        return self::success(new DepartmentResource($department), 'Department created successfully.', 201);

    }

    /**
     * Display the specified department.
     *
     * @param Department $department
     * @return JsonResponse
     */
    public function show(Department $department): JsonResponse
    {
        return self::success(new DepartmentResource($department->load('image', 'tables')), 'Department retrieved successfully.');

    }

    /**
     * Update the specified department in storage.
     *
     * @param UpdateDepartmentRequest $request
     * @param Department $department
     * @return JsonResponse
     */
    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $department = $this->departmentService->updateDepartment($department, $request->validated());

        return self::success(new DepartmentResource($department), 'Department updated successfully.');

    }

    /**
     * Remove the specified department from storage.
     *
     * @param Department $department
     * @return JsonResponse
     */
    public function destroy(Department $department): JsonResponse
    {
        $this->departmentService->deleteDepartment($department);
       
        return self::success(null, 'Department deleted successfully.');
    }

    
 /**
     * Retrieve a list of soft-deleted departments.
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function showDeleted(): JsonResponse
    {
        try {
            $softDeleted = $this->departmentService->getDeletedDepartments();

            if ($softDeleted->isEmpty()) {
                return self::error(null, 'No deleted departments found.', 404);
            }

            return self::success($softDeleted, 'Soft-deleted departments retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Error retrieving soft-deleted departments: ' . $e->getMessage());
            return self::error(null, 'An error occurred while retrieving deleted departments.', 500);
        }
    }

    /**
     * Restore a soft-deleted department by its ID.
     *
     * @param string $id The ID of the department to restore.
     * @return JsonResponse
     *
     * @throws ModelNotFoundException If the department with the given ID is not found.
     * @throws Exception If an error occurs during the restoration process.
     */
    public function restoreDeleted(string $id): JsonResponse
    {
        try {
            $department = $this->departmentService->restoreDeletedDepartment($id);
            return self::success($department, 'Department restored successfully.');
        } catch (ModelNotFoundException $e) {
            return self::error(null, 'Department not found.', 404);
        } catch (Exception $e) {
            Log::error('Error restoring department: ' . $e->getMessage());
            return self::error(null, 'An error occurred while restoring the department.', 500);
        }
    }

    /**
     * Permanently delete a soft-deleted department by its ID.
     *
     * @param string $id The ID of the department to permanently delete.
     * @return JsonResponse
     *
     * @throws ModelNotFoundException If the department with the given ID is not found.
     * @throws Exception If an error occurs during the permanent deletion process.
     */
    public function forceDeleted(string $id): JsonResponse
    {
        try {
            $this->departmentService->permanentlyDeleteDepartment($id);
            return self::success(null, 'Department permanently deleted.');
        } catch (ModelNotFoundException $e) {
            return self::error(null, 'Department not found.', 404);
        } catch (Exception $e) {
            Log::error('Error permanently deleting department: ' . $e->getMessage());
            return self::error(null, 'An error occurred while permanently deleting the department.', 500);
        }
    }
    
}
