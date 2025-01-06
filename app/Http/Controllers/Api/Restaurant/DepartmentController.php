<?php

namespace App\Http\Controllers\Api\Restaurant;

use Exception;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use App\Services\DepartmentService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Http\Requests\ImageRequest\StoreImageRequest;
use App\Http\Requests\Department\StoreDepartmentRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Department\UpdateDepartmentRequest;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $departments = $this->departmentService->getAllDepartments();
                // Return JSON response containing the departments data.

        return self::paginated($departments, DepartmentResource::class, 'Departments retrieved successfully.',200);
    }



    /**
     * Store a newly created department in storage.
     *
     * @param StoreDepartmentRequest $request
     * @return JsonResponse
     */
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $department = $this->departmentService->createDepartment($data);
        return self::success(new DepartmentResource($department), 'Department created successfully.', 201);
    }

    /**
     * Display the specified department.
     *
     * @param Department $department
     * @return JsonResponse
     */


    public function show($id): JsonResponse
    {
        $department = Department::with('image', 'tables', 'manager')->find($id);
        return self::success(new DepartmentResource($department,'Department retrieved successfully.'));
    }

    /**
     *
     * Handle the request to update a department.
     *
     * This method receives the validated data from the request, including the department's
     * details and optional images. It then calls the service method to perform the update.
     *
     * @param UpdateDepartmentRequest $request The request object containing the validated data.
     * @param Department $department The department to be updated.
     * @return JsonResponse A JSON response indicating the success or failure of the update.
     */
    public function update(UpdateDepartmentRequest $request, Department $department) : JsonResponse
    {
        // Get the validated data from the request
        $data = $request->validated();
        // Get the uploaded images from the request

        // Pass the data and images to the service method to update the department
        $updatedDepartment = $this->departmentService->updateDepartment($department, $data);
        // Return a successful response with the updated department data
        return self::success(new DepartmentResource($updatedDepartment), 'Department updated successfully.', 200);
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
        *
        * @throws Exception
        */
        public function showDeleted(): JsonResponse
        {
            try {
                $softDeleted = Department::whereNotNull('deleted_at')->get();

                if ($softDeleted->isEmpty()) {
                    return self::error(null, 'No deleted Department found.', 404);
                }

                return self::success($softDeleted, 'Soft-deleted Departments retrieved successfully.');
            } catch (\Exception $e) {
                // Log the error
                \Log::error('Error retrieving soft-deleted Departments: ' . $e->getMessage());
                return self::error(null, 'An error occurred while retrieving deleted Departments.', 500);
            }
        }







    /**
     * Soft delete an image associated with a department.
     *
     * @param  string  $departmentId  The ID of the department the image belongs to.
     * @param  string  $imageId  The ID of the image to be soft deleted.
     * @return \Illuminate\Http\JsonResponse
     */
    public function softdeletImage($departmentId, $imageId)
    {
        // Call the service method to soft delete the image
        $this->departmentService->softDeleteDepartmentImage($imageId, $departmentId);
        // Return a success response
        return self::success(null, 'Image soft deleted.');
    }

    /**
     * Show all soft deleted images.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showDeletedImage()
    {
        // Get all deleted images from the service
        $images = $this->departmentService->getDeletedImage();
        // Return a success response with the retrieved images
        return self::success($images, 'Images deleted.', 200);
    }


    /**
     * Restore a soft-deleted image associated with a department.
     *
     * @param  string  $departmentId  The ID of the department the image belongs to.
     * @param  string  $imageId  The ID of the image to be restored.
     * @return \Illuminate\Http\JsonResponse
     */
    public function restoreImage($departmentId, $imageId)
    {
        // Call the service method to restore the soft-deleted image
        $this->departmentService->restoreDepatmentImage($imageId, $departmentId);
        // Return a success response
        return self::success(null, 'Image restored.');
    }

    /**
     * Permanently delete an image associated with a department.
     *
     * @param  string  $departmentId  The ID of the department the image belongs to.
     * @param  string  $imageId  The ID of the image to be permanently deleted.
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletImage($departmentId, $imageId)
    {
        // Call the service method to permanently delete the image
        $this->departmentService->permanentlyDeleteImage($imageId, $departmentId);
        // Return a success response
        return self::success(null, 'Image permanently deleted.');
    }

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
