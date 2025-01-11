<?php
namespace App\Services;

use Exception;
use App\Models\Email;
use App\Models\Image;
use App\Models\Restaurant;
use App\Models\PhoneNumber;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RestaurantService
{
    /**
     * Fetch all restaurants with their associated emails and phone numbers.
     *
     * This method retrieves all restaurant records along with their associated emails and phone numbers.
     *
     * @return \Illuminate\Database\Eloquent\Collection A collection of restaurants with their emails and phone numbers.
     * @throws HttpException If unable to fetch restaurant data.
     */
    public function getRestaurantdata()
    {
        try {
            // Attempt to fetch all restaurants with associated emails and phone numbers
            $restaurant = Cache::remember('restaurant', 1440, function () {
                return Restaurant::with(["emails", "phoneNumbers", "images"])->get();
            });
            return $restaurant;
        } catch (Exception $e) {
            // Log the error and throw an exception if there is a failure in fetching restaurant data
            Log::error('Cannot get restaurant data: ' . $e->getMessage());
            throw new HttpException(500, 'Cannot get restaurant data');
        }
    }

    /**
     * Store a new restaurant, including emails, phone numbers, and images.
     *
     * This method creates a new restaurant record along with any associated emails, phone numbers, and images.
     * It ensures that all relationships (emails, phone numbers, and images) are saved correctly.
     *
     * @param array $data The restaurant data, including emails, phone numbers, and images.
     * @return Restaurant The created restaurant instance with its associated data.
     * @throws HttpException If unable to insert restaurant data.
     */

    public function storeRestaurantData($data)
    {
        try {
            // Begin a transaction
            DB::beginTransaction();
            if (Restaurant::count() > 0) {
                throw new HttpResponseException(response()->json([
                    'status' => 'error',
                    'message' => 'Cannot insert restaurant data: Only one restaurant allowed.',
                ], 500));
            }
            // Create the restaurant record in the database
            $restaurant = Restaurant::create($data);
            // Add associated emails if provided
            if (isset($data['emails']) && !empty($data['emails'])) {
                foreach ($data['emails'] as $email) {
                    $restaurant->emails()->create([
                        'email' => $email['email'],
                        'description' => $email['description'] ?? null,
                    ]);
                }
            }
            // Add associated phone numbers if provided
            if (isset($data['PhoneNumbers']) && !empty($data['PhoneNumbers'])) {
                foreach ($data['PhoneNumbers'] as $number) {
                    $restaurant->phoneNumbers()->create([
                        'PhoneNumber' => $number['PhoneNumber'],
                        'description' => $number['description'] ?? null,
                    ]);
                }
            }
            // Handle image uploads if provided
            if (isset($data['images']) && !empty($data['images'])) {
                $images = $data['images'];
                foreach ($images as $image) {
                    // Generate a unique name for the image
                    $imageName = Str::random(32);
                    $extension = $image->getClientOriginalExtension();
                    $filePath = "Images/{$imageName}.{$extension}";
                    // Store the image on disk
                    $path = Storage::putFileAs('Images', $image, "{$imageName}.{$extension}");
                    $url = Storage::url($path);
                    $mime_type = $image->getClientMimeType();

                    // Store image data related to the restaurant
                    $restaurant->images()->create([
                        'mime_type' => $mime_type,
                        'image_path' => $url,
                        'name' => $imageName,
                    ]);
                }
            }
            // Commit the transaction if all is well
            DB::commit();
            Cache::forget('restaurant');

            return $data;
        } catch (HttpResponseException $e) {
            // Handle specific HttpResponseException
            Log::error('HttpResponseException: ' . $e->getMessage());
            throw $e;
        } catch (Exception $e) {
            // Log and throw an error if there is any issue inserting the restaurant data
            Log::error('Cannot insert restaurant data: ' . $e->getMessage());
            throw new HttpException(500, 'Cannot insert restaurant data: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing restaurant's data, including emails and phone numbers.
     *
     * This method allows updating the details of a restaurant, including its emails, phone numbers
     *
     * @param Restaurant $restaurant The restaurant instance to be updated.
     * @param array $data The new data to update, including emails and phone numbers.
     * @return Restaurant The updated restaurant instance.
     * @throws HttpException If unable to update restaurant data.
     */
    public function updateRestaurantdata(Restaurant $restaurant, $data)
    {
        try {
            // Begin a transaction
            DB::beginTransaction();
            // Update the restaurant with new data
            $restaurant->update($data);
            // Update emails if provided
            if (isset($data['emails']) && !empty($data['emails'])) {
                $emails = $data['emails'];
                foreach ($emails as $email) {
                    // Create new email records related to the restaurant
                    $restaurant->emails()->create([
                        'email' => $email['email'],
                        'description' => $email['description'] ?? null,
                    ]);
                }
            }
            // Update phone numbers if provided
            if (isset($data['PhoneNumbers']) && !empty($data['PhoneNumbers'])) {
                foreach ($data['PhoneNumbers'] as $number) {
                    $restaurant->phoneNumbers()->create([
                        'PhoneNumber' => $number['PhoneNumber'],
                        'description' => $number['description'] ?? null,
                    ]);
                }
            }
            // Handle image uploads if provided
            if (isset($data['images']) && !empty($data['images'])) {
                $images = $data['images'];
                foreach ($images as $image) {
                    // Generate a unique name for the image
                    $imageName = Str::random(32);
                    $extension = $image->getClientOriginalExtension();
                    $filePath = "Images/{$imageName}.{$extension}";
                    // Store the image on disk
                    $path = Storage::putFileAs('Images', $image, "{$imageName}.{$extension}");
                    $url = Storage::url($path);
                    $mime_type = $image->getClientMimeType();
                    // Store image data related to the restaurant
                    $restaurant->images()->create([
                        'mime_type' => $mime_type,
                        'image_path' => $url,
                        'name' => $imageName,
                    ]);
                }
            }
            // Commit the transaction if all is well
            DB::commit();
            return $restaurant;
        } catch (Exception $e) {
            // Log and throw an error if there is any issue updating the restaurant data
            Log::error('Cannot update restaurant data: ' . $e->getMessage());
            throw new HttpException(500, 'Cannot update restaurant data: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete a restaurant's data.
     *
     * This method  deletes a restaurant
     *
     * @param Restaurant $restaurant The restaurant instance to be deleted.
     * @return bool
     * @throws HttpException If unable to delete restaurant data.
     */
    public function DeleteRestaurantdata(Restaurant $restaurant)
    {
        try {
            // Begin a transaction
            DB::beginTransaction();
            // delete the restaurant
            $restaurant->delete();
            $restaurant->images()->forceDelete();
            // Commit the transaction if all is well
            DB::commit();

            return true;
        } catch (Exception $e) {
            // Log and throw an error if there is any issue deleting the restaurant
            Log::error('Cannot delete restaurant data: ' . $e->getMessage());
            throw new HttpException(500, 'Cannot delete restaurant data');
        }
    }

    /**
     * Delete an email by its ID.
     *
     * This method attempts to find the email by its ID and deletes it from the database.
     * @param int $id The email ID to be deleted.
     * @return bool
     * @throws HttpResponseException If the email is not found or cannot be deleted.
     */
    public function deleteEmail($id)
    {
        try {
            // Attempt to find the email by its ID and delete it
            $email = Email::findOrFail($id);
            $email->delete();
            return true;
        } catch (ModelNotFoundException $e) {
            // Log and throw error if email is not found
            Log::error('Email not found: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Email not found',
            ], 404));
        } catch (Exception $e) {
            // Log and throw a general error if the deletion fails
            Log::error('Error deleting email  ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Unable to delete email.',
            ], 500));
        }
    }

    /**
     * Delete a phone number by its ID.
     *
     * This method attempts to find the phone number by its ID and deletes it from the database.
     *
     * @param int $id The phone number ID to be deleted.
     * @return bool
     * @throws HttpResponseException If the phone number is not found or cannot be deleted.
     */
    public function deletePhoneNumber($id)
    {
        try {
            // Attempt to find the phone number by its ID and delete it
            $phoneNumber = PhoneNumber::findOrFail($id);
            $phoneNumber->delete();
            // Commit the transaction if all is well
            DB::commit();
            return true;
        } catch (ModelNotFoundException $e) {
            // Log and throw error if phone number is not found
            Log::error('Phone Number not found: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Phone number not found',
            ], 404));
        } catch (Exception $e) {
            // Log and throw a general error if the deletion fails
            Log::error('Error deleting phone number' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Unable to delete phone number.',
            ], 500));
        }
    }

    /**
 * Soft delete an image associated with a restaurant.
 *
 * This method attempts to find and soft delete an image for a specific restaurant.
 *
 * @param string $imageId The ID of the image to be deleted.
 * @param string $restaurantId The ID of the restaurant the image belongs to.
 * @return bool Indicates whether the deletion was successful.
 * @throws HttpResponseException If the image is not found or cannot be deleted.
 */
    public function softDeleteRestaurantImage(string $imageId, string $restaurantId)
    {
        try {
            // Attempt to find the restaurant by its ID
            $restaurant = Restaurant::findOrFail($restaurantId);
            // Attempt to find the image within the restaurant's images
            $image = $restaurant->images()->findOrFail($imageId);

            // Soft delete the image (mark it as deleted without removing from database)
            $image->delete();
            return true;
        } catch (ModelNotFoundException $e) {
            if ($e->getModel() === Restaurant::class) {
                $errorMessage = 'Restaurant not found';
            } elseif ($e->getModel() === Image::class) {
                $errorMessage = 'Image not found ';
            }
            // Log and throw error if the restaurant or image is not found
            Log::error($errorMessage . '. Error: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => $errorMessage,
            ], 404));

        } catch (Exception $e) {
            // Log and throw a general error if soft deletion fails
            Log::error('Error soft deleting image with ID: ' . $imageId . ' for restaurant ID: ' . $restaurantId . '. Error: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error soft deleting image: ' . $e->getMessage(),
            ], 500));
        }
    }

    /**
     * Get all soft-deleted images associated with restaurants.
     *
     * This method retrieves all restaurants and their associated images that have been soft-deleted.
     *
     * @return \Illuminate\Database\Eloquent\Collection A collection of restaurants with their soft-deleted images.
     * @throws HttpResponseException If there is an error retrieving the images.
     */
    public function getDeletedImages()
    {
        try {
            // Get restaurants with soft-deleted images
            $restaurants = Restaurant::with(['images' => function ($query) {
                $query->onlyTrashed();
            }])->get();
            return $restaurants;
        } catch (Exception $e) {
            // Log and throw error if fetching the deleted images fails
            Log::error('Error fetching deleted images for restaurants: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Unable to fetch deleted images for restaurants.',
            ], 500));
        }
    }

    /**
     * Restore a soft-deleted image associated with a restaurant.
     *
     * This method attempts to restore a soft-deleted image for a specific restaurant.
     *
     * @param string $imageId The ID of the image to be restored.
     * @param string $restaurantId The ID of the restaurant the image belongs to.
     * @return bool Indicates whether the restoration was successful.
     * @throws HttpResponseException If the image is not found or cannot be restored.
     */
    public function restoreRestaurantImage(string $imageId, string $restaurantId)
    {
        try {

            // Attempt to find the restaurant by its ID
            $restaurant = Restaurant::findOrFail($restaurantId);
            // Attempt to find the soft-deleted image within the restaurant's images
            $image = $restaurant->images()->onlyTrashed()->findOrFail($imageId);
            // Restore the soft-deleted image
            $image->restore();
            return true;
        } catch (ModelNotFoundException $e) {
            if ($e->getModel() === Restaurant::class) {
                $errorMessage = 'Restaurant not found';
            } elseif ($e->getModel() === Image::class) {
                $errorMessage = 'Image not found ';
            }
            // Log and throw error if the restaurant or image is not found
            Log::error($errorMessage . '. Error: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => $errorMessage,
            ], 404));
        } catch (Exception $e) {
            // Log and throw a general error if restoring the image fails
            Log::error('Error restoring image: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error restoring image',
            ], 500));
        }
    }

    /**
     * Permanently delete an image associated with a restaurant.
     *
     * This method attempts to permanently delete an image (remove from database) for a specific restaurant.
     *
     * @param string $imageId The ID of the image to be permanently deleted.
     * @param string $restaurantId The ID of the restaurant the image belongs to.
     * @return bool Indicates whether the permanent deletion was successful.
     * @throws HttpResponseException If the image is not found or cannot be permanently deleted.
     */
    public function permanentlyDeleteImage(string $imageId, string $restaurantId)
    {
        try {
            // Attempt to find the restaurant by its ID
            $restaurant = Restaurant::findOrFail($restaurantId);
            // Attempt to find the soft-deleted image within the restaurant's images
            $image = $restaurant->images()->onlyTrashed()->find($imageId);
            // Get the image path from the database record
            $imagePath = $image->image_path;
            // Permanently delete the image (remove from the database)
            $image->forceDelete();
            return true;
        } catch (ModelNotFoundException $e) {
            if ($e->getModel() === Restaurant::class) {
                $errorMessage = 'Restaurant not found';
            } elseif ($e->getModel() === Image::class) {
                $errorMessage = 'Image not found ';
            }
            // Log and throw error if the restaurant or image is not found
            Log::error($errorMessage . '. Error: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => $errorMessage,
            ], 404));
        } catch (Exception $e) {
            // Log and throw a general error if permanently deleting the image fails
            Log::error('Error permanently deleting image: ' . $e->getMessage());
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Error permanently deleting image',
            ], 500));
        }
    }
}
