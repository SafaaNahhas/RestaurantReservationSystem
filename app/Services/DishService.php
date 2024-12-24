<?php

namespace App\Services;

use Exception;
use App\Models\Dish;
use App\Models\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPOpenSourceSaver\JWTAuth\Contracts\Providers\Auth;

class DishService
{


    /**
     * List all Dish
     */
    public function listDish($per_page)
    {
        try {
            return Dish::with('category')->paginate($per_page);
        } catch (Exception $e) {
            Log::error('Error in Get all Dishes'. $e->getMessage());
            throw new HttpResponseException(response()->json(
                [
                   'status' => 'error',
                   'message' => "there is something wrong in server",
            ],
                500
            ));
        }
    }
    /**
    * Create a new Dish.
    *
    * @param array $Dish
    * @return \App\Models\Dish
    */
    public function createDish(array $data)
    {
        try {
            DB::beginTransaction();
            // Create a new Dish record with the provided data
            $dish = Dish::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'],
            ]);
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
                    $dish->image()->create([
                        'mime_type' => $mime_type,
                        'image_path' => $url, // Full path to the image
                        'name' => $imageName, // Randomly generated image name
                    ]);
                }
            }
            // Commit the transaction if all is well
            DB::commit();
            // Return the created dish
            return $dish;
        } catch (Exception $e) {
            DB::rollBack(); // Rollback the transaction on error
            Log::error('Error creating Dish: ' . $e->getMessage());
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "There is something wrong with the server",
                ],
                500
            ));
        }
    }

    /**
    * Get the details of a specific Dish by its ID.
    *
    * @param int $id
    * @return \App\Models\Dish
    */
    public function getDish(int $id)
    {
        try {
            DB::beginTransaction();
            // Find the Dish by ID or fail with a 404 error if not found
            $dish= Dish::findOrFail($id);
            return $dish->load('category');
        } catch (ModelNotFoundException $e) {
            Log::error("error in get a Dish" . $e->getMessage());
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));

        } catch (Exception $e) {
            Log::error("error in get a Dish" . $e->getMessage());
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }


    /**
    * Update the details of a specific book.
    *
    * @param array $data
    * @param int $id
    * @return \App\Models\Book
    */
    public function updateDish(array $data, int $id)
    {
        try {
            // Find the dish by ID or fail with a 404 error if not found
            $dish = Dish::findOrFail($id);

            // Update the dish with the provided data, filtering out null values
            $dish->update(array_filter([
                'name'=> $data['name'] ?? $dish->Dish_name,
                'description'=> $data['description'] ?? $dish->description,
                'category_id'=> $data['category_id'] ?? $dish->category_id,
            ]));
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
                    $dish->image()->create([
                        'mime_type' => $mime_type,
                        'image_path' => $url, // Full path to the image
                        'name' => $imageName, // Randomly generated image name
                    ]);
                }
            }
            // Commit the transaction if all is well
            DB::commit();

            // Return the updated dish
            return $dish;
        } catch (ModelNotFoundException $e) {
            Log::error("error" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));

        } catch (Exception $e) {
            Log::error("error in update Dish" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }


    /**
     * Delete a specific Dish by its ID.
     *
     * @param int $id
     * @return void
     */
    public function deleteDish(int $id)
    {
        try {
            // Find the Dish by ID or fail with a 404 error if not found
            $Dish = Dish::findOrFail($id);
            $Dish->image()->delete();
            // Delete the Dish
            $Dish->delete();
        } catch (ModelNotFoundException $e) {
            Log::error("error" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));

        } catch (Exception $e) {
            Log::error("error in delete a Dish" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
    /**
    * Display a paginated listing of the trashed (soft deleted) resources.
    */
    public function trashedListDish($perPage)
    {
        try {
            return Dish::onlyTrashed()->paginate($perPage);
        } catch (Exception $e) {
            Log::error("error in display list of trashed Dish" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
    /**
     * Restore a trashed (soft deleted) resource by its ID.
     *
     * @param  int  $id  The ID of the trashed Task to be restored.
     * @return \App\Models\Task
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the Task with the given ID is not found.
     * @throws \Exception If there is an error during the restore process.
     */
    public function restoreDish($id)
    {
        try {
            $Dish = Dish::onlyTrashed()->findOrFail($id);
            $Dish->restore();
            $Dish->image()->restore();
            return $$Dish;
        } catch (ModelNotFoundException $e) {
            Log::error("error" . $e->getMessage());
            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));

        } catch (Exception $e) {
            Log::error("error in restore a Dish" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }

    /**
     * Permanently delete a trashed (soft deleted) resource by its ID.
     *
     * @param  int  $id  The ID of the trashed Task to be permanently deleted.
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the Task with the given ID is not found.
     * @throws \Exception If there is an error during the force delete process.
     */
    public function forceDeleteDish($id)
    {
        try {
            $Dish = Dish::onlyTrashed()->findOrFail($id);
            $Dish->image()->forceDelete();

            $Dish->forceDelete();
        } catch (ModelNotFoundException $e) {
            Log::error("error" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));

        } catch (Exception $e) {
            Log::error("error  in forceDelete Dish" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }


    /**
 * Soft delete an image associated with a dish.
 *
 * @param  string  $imageId  The ID of the image to be deleted.
 * @param  string  $dishId  The ID of the dish the image belongs to.
 * @return bool  Indicates whether the deletion was successful.
 */
    public function softDeleteDishImage(string $imageId, string $dishId)
    {
        try {
            // Find the dish by its ID
            $dish = Dish::findOrFail($dishId);
            // Find the image associated with the dish by its ID
            $image = $dish->image()->findOrFail($imageId);
            // Soft delete the image
            $image->delete();
            // Return true to indicate the operation was successful
            return true;
        } catch (ModelNotFoundException $e) {
            if ($e->getModel() === Dish::class) {
                $errorMessage = 'Dish not found';
            } elseif ($e->getModel() === Image::class) {
                $errorMessage = 'Image not found ';
            }
            // Log and throw error if the restaurant or image is not found
            Log::error($errorMessage . '. Error: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => $errorMessage,
            ], 404));
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error soft deleting image: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error soft deleting image',
            ], 500));
        }
    }
    /**
     * Get all images (including permanently deleted) associated with all dishes.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDeletedImage()
    {
        try {
            $dishes = Dish::with(['image' => function ($query) {
                $query->onlyTrashed(); // Retrieve soft deleted images
            }])->get();
            return $dishes;
        } catch (Exception $e) {
            Log::error('Error fetching images including deleted images for all dishes: ' . $e->getMessage());
            throw new \RuntimeException('Unable to fetch images including deleted images for all dishes.');
        }
    }
    /**
     * Restore a soft-deleted image associated with a dish.
     *
     * @param  string  $imageId  The ID of the image to be restored.
     * @param  string  $dishId  The ID of the dish the image belongs to.
     * @return bool  Indicates whether the restoration was successful.
     */
    public function restoreDishImage(string $imageId, string $dishId)
    {
        try {
            // Find the dish by its ID
            $dish = Dish::findOrFail($dishId);
            // Find the image associated with the dish by its ID
            $image = $dish->image()->onlyTrashed()->findOrFail($imageId);
            // Restore the soft-deleted image
            $image->restore();
            // Return true to indicate the operation was successful
            return true;
        } catch (ModelNotFoundException $e) {
            if ($e->getModel() === Dish::class) {
                $errorMessage = 'Dish not found';
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
            Log::error('Error restoring image: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error restoring image',
            ], 500));
        }
    }


    /**
     * Permanently delete an image associated with a dish.
     *
     * @param  string  $imageId  The ID of the image to be permanently deleted.
     * @param  string  $dishId  The ID of the dish the image belongs to.
     * @return bool  Indicates whether the permanent deletion was successful.
     */
    public function permanentlyDeleteImage(string $imageId, string $dishId)
    {
        try {
            // Find the dish by its ID
            $dish = Dish::findOrFail($dishId);
            // Find the image associated with the dish by its ID
            $image = $dish->image()->onlyTrashed()->find($imageId);
            // Permanently delete the image
            $image->forceDelete();
            // Return true to indicate the operation was successful
            return true;
        } catch (ModelNotFoundException $e) {
            if ($e->getModel() === Dish::class) {
                $errorMessage = 'Dish not found';
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
            Log::error('Error permanently deleting image: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error permanently deleting image',
            ], 500));
        }
    }

}
