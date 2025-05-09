<?php

namespace App\Services\Department;

use Exception;
use App\Models\User;
use App\Models\Image;

use RuntimeException;
use App\Models\Department;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
    public function getAllDepartments($per_page = 10)
    {
        try {
            return Department::paginate(10);
        } catch (Exception $e) {
            Log::error('Error fetching departments: ' . $e->getMessage(), [
                'exception' => $e,
                'per_page' => $per_page
            ]);
            throw new \RuntimeException('Unable to fetch departments.');
        }
    }


    /**
     * Create a new department.
     *
     * @param array $data
     * @param array|null $images
     * @return Department
     */
    public function createDepartment(array $data)
    {


        try {
            // Begin a transaction
            DB::beginTransaction();
            $this->validateManager($data['manager_id'] ?? null);
            // Create the department
            $department = Department::create($data);
            // Handle images if they exist
            if (isset($data['images']) && !empty($data['images'])) {
                $images = $data['images'];
                foreach ($images as $image) {
                    $imageName = Str::random(32);
                    // Get the file extension (e.g., .jpg, .png)
                    $extension = $image->getClientOriginalExtension();
                    // Define the file path for storing the image
                    $filePath = "Images/{$imageName}.{$extension}";
                    // Store the image securely in the 'public' disk storage
                    $path = Storage::putFileAs('Images', $image, "{$imageName}.{$extension}");
                    // Get the full URL to the stored image

                    $url = Storage::url($path);
                    // Get the MIME type of the image
                    $mime_type = $image->getClientMimeType();
                    // Associate the image with the department and store it in the 'images' table
                    $department->image()->create([
                        'mime_type' => $mime_type,
                        'image_path' => $url, // Full path to the image
                        'name' => $imageName, // Randomly generated image name
                    ]);
                }
            }
            // Commit the transaction if all is well
            DB::commit();
            // Return the created department
            return $department;
        } catch (Exception $e) {
            DB::rollBack(); // Roll back the transaction if any error occurs

            Log::error('Error creating department: ' . $e->getMessage());
            throw new \RuntimeException('Unable to create department: ' . $e->getMessage());
        }
    }

     /**
     * Retrieve an department by ID with its reservations.
     *
     * @param int $id
     * @return Department|null
     */
    public function getDepartmentById(int $id)
    {
        try {
            // Attempt to find the department
        $department = Department::findOrFail($id);

        // Load related image, tables, and manager
        $department->load('image', 'tables', 'manager');
        if (!$department) {
            return response()->json('Department not found.');
        }
            return $department;
        }  catch (\Exception $e) {
            // Handle any other unexpected errors
            throw new \Exception("An error occurred while retrieving the department: " . $e->getMessage());
        }
    }

    /**
     * Update an existing department.
     *
     * This method updates the department's basic details (such as name, description, etc.)
     *
     * @param Department $department The department object to be updated.
     * @param array $data The validated data to update the department's attributes.
     * @param array|null $images Optional array of image files to be added to the department.
     * @return Department The updated department object.
     * @throws \RuntimeException If there is an error during the update process.
     */
    public function updateDepartment(Department $department, array $data)
    {
        try {
            // Begin a transaction
            DB::beginTransaction();
            // Update the department's main data (name, description, etc.)
            if (isset($data['manager_id'])) {
                $this->validateManager($data['manager_id']);
            }
            $department->update($data);
            // Handle images if they exist
            if (isset($data['images']) && !empty($data['images'])) {
                $images = $data['images'];
                foreach ($images as $image) {
                    $imageName = Str::random(32);
                    // Get the file extension (e.g., .jpg, .png)
                    $extension = $image->getClientOriginalExtension();
                    // Define the file path for storing the image
                    $filePath = "Images/{$imageName}.{$extension}";
                    // Store the image securely in the 'public' disk storage
                    $path = Storage::putFileAs('Images', $image, "{$imageName}.{$extension}");
                    // Get the full URL to the stored image

                    $url = Storage::url($path);
                    // Get the MIME type of the image
                    $mime_type = $image->getClientMimeType();
                    // Associate the image with the department and store it in the 'images' table
                    $department->image()->create([
                        'mime_type' => $mime_type,
                        'image_path' => $url, // Full path to the image
                        'name' => $imageName, // Randomly generated image name
                    ]);
                }
            }

            DB::commit(); // Commit the transaction if all is well
            // Return the updated department
            return $department;

        } catch (Exception $e) {
            // Log any errors during the process
            Log::error('Error updating department: ' . $e->getMessage());
            // Throw a runtime exception with a relevant error message
            throw new \RuntimeException('Unable to update department: ' . $e->getMessage());
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
            //soft delet department
            $department->delete();
            //soft delet image
            $department->image()->delete();
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
    public function AllDeleted()
    {
        $department=Department::onlyTrashed()->get();
        return $department;
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
        $department->image()->restore();
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
        $department->image()->forceDelete();
    }


    /**
     * Soft delete an image associated with a department.
     *
     * @param  string  $imageId  The ID of the image to be deleted.
     * @param  string  $departmentId  The ID of the department the image belongs to.
     * @return bool  Indicates whether the deletion was successful.
     */
    public function softDeleteDepartmentImage(string $imageId, string $departmentId)
    {
        try {
            // Find the department by its ID
            $department = Department::findOrFail($departmentId);
            // Find the image associated with the department by its ID
            $image = $department->image()->findOrFail($imageId);
            // Soft delete the image
            $image->delete();
            // Return true to indicate the operation was successful
            return true;
        } catch (ModelNotFoundException $e) {
            if ($e->getModel() === Department::class) {
                $errorMessage = 'Department not found';
            } elseif ($e->getModel() === Image::class) {
                $errorMessage = 'Image not found ';
            }
            // Log and throw error if the restaurant or image is not found
            Log::error($errorMessage . '. Error: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => $errorMessage,
            ], 404));
        } catch (\Exception $e) {
            // Log any unexpected errors
            Log::error('Error soft deleting image: ' . $e->getMessage());
            // Return a general error response in case of an unexpected issue
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error soft deleting image',
            ], 500));
        }
    }

    /**
     * Get all images (including permanently deleted) associated with all departments.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDeletedImage()
    {
        try {
            // Retrieve all images associated with all departments, including soft deleted images
            $department = Department::with(['image' => function ($query) {
                $query->onlyTrashed(); // Retrieve soft deleted and permanently deleted images
            }])->get();
            return $department;
        } catch (Exception $e) {
            Log::error('Error fetching images including deleted images for all departments: ' . $e->getMessage());
            throw new \RuntimeException('Unable to fetch images including deleted images for all departments.');
        }
    }
    /**
     * Restore a soft-deleted image associated with a department.
     *
     * @param  string  $imageId  The ID of the image to be restored.
     * @param  string  $departmentId  The ID of the department the image belongs to.
     * @return bool  Indicates whether the restoration was successful.
     */
    public function restoreDepatmentImage(string $imageId, $departmentId)
    {
        try {
            // Find the department by its ID
            $department = Department::findOrFail($departmentId);
            // Find the image associated with the department by its ID
            $image = $department->image()->onlyTrashed()->findOrFail($imageId);
            // Restore the soft-deleted image
            $image->restore();
            // Return true to indicate the operation was successful
            return true;
        } catch (ModelNotFoundException $e) {
            if ($e->getModel() === Department::class) {
                $errorMessage = 'Department not found';
            } elseif ($e->getModel() === Image::class) {
                $errorMessage = 'Image not found ';
            }
            // Log and throw error if the restaurant or image is not found
            Log::error($errorMessage . '. Error: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => $errorMessage,
            ], 404));
        } catch (\Exception $e) {
            // Log any unexpected errors
            Log::error('Error restoring image: ' . $e->getMessage());
            // Return a general error response in case of an unexpected issue
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error restoring image',
            ], 500));
        }
    }

    /**
     * Permanently delete an image associated with a department.
     *
     * @param  string  $imageId  The ID of the image to be permanently deleted.
     * @param  string  $departmentId  The ID of the department the image belongs to.
     * @return bool  Indicates whether the permanent deletion was successful.
     */
    public function permanentlyDeleteImage(string $imageId, $departmentId)
    {
        try {
            // Find the department by its ID
            $department = Department::findOrFail($departmentId);
            // Find the image associated with the department by its ID
            $image = $department->image()->onlyTrashed()->findOrFail($imageId);
            // If the image is not found, return a clear error response
            // Permanently delete the image
            $image->forceDelete();
            // Return true to indicate the operation was successful
            return true;
        } catch (ModelNotFoundException $e) {
            if ($e->getModel() === Department::class) {
                $errorMessage = 'Department not found';
            } elseif ($e->getModel() === Image::class) {
                $errorMessage = 'Image not found ';
            }
            // Log and throw error if the restaurant or image is not found
            Log::error($errorMessage . '. Error: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => $errorMessage,
            ], 404));
        } catch (\Exception $e) {
            // Log any unexpected errors
            Log::error('Error permanently deleting image: ' . $e->getMessage());
            // Return a general error response in case of an unexpected issue
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error permanently deleting image',
            ], 500));
        }
    }


    /**
 * Validate the manager ID and ensure the user has the "Manager" role.
 *
 * @param int|null $managerId
 * @throws \RuntimeException
 */
private function validateManager($managerId)
{
    if (!$managerId) {
        throw new \RuntimeException('Manager ID is required.');
    }

    $manager = User::find($managerId);

    if (!$manager) {
        throw new \RuntimeException('Invalid manager ID.');
    }

    if (!$manager->hasRole('Manager')) {
        throw new \RuntimeException('The selected user is not a Manager.');
    }
}
}
