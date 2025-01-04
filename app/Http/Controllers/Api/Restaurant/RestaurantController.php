<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Models\Email;
use App\Models\Restaurant;
use App\Models\PhoneNumber;
use Illuminate\Http\Request;
use App\Services\RestaurantService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRatingRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\RestaurantRequest\StoreRestaurantRequest;
use App\Http\Requests\RestaurantRequest\UpdatRestaurantRequest;
use Exception;
use Illuminate\Support\Facades\Log;

class RestaurantController extends Controller
{
    // Dependency Injection: Injecting the RestaurantService to handle business logic
    protected $restaurantService;

    public function __construct(RestaurantService $restaurantService)
    {
        $this->restaurantService = $restaurantService;
    }

    /**
     * Display a listing of the restaurant resources.
     *
     * @return \Illuminate\Http\JsonResponse A list of restaurants with their associated data.
     */
    public function index()
    {
        // Retrieve restaurant data using the service
        $restaurant = $this->restaurantService->getRestaurantdata();
        return self::success($restaurant, 'Restaurant data');
    }

    /**
     * Store a new restaurant
     *
     * @param StoreRestaurantRequest $request The validated request data for storing a new restaurant.
     * @return \Illuminate\Http\JsonResponse The created restaurant data.
     */
    public function store(StoreRestaurantRequest $request)
    {
        // Validate and get the data from the request
        $validateddata = $request->validated();

        // Call the service to store the restaurant data
        $restaurant = $this->restaurantService->storeRestaurantdata($validateddata);

        // Return success response with the created restaurant
        return self::success($restaurant, 'Restaurant data created');
    }

    /**
     * Update the specified restaurant resource in storage.
     *
     * @param UpdatRestaurantRequest $request The validated request data for updating a restaurant.
     * @param Restaurant $restaurant The existing restaurant to be updated.
     * @return \Illuminate\Http\JsonResponse The updated restaurant data.
     */
    public function update(UpdatRestaurantRequest $request, Restaurant $restaurant)
    {
        // Validate and get the data from the request
        $validateddata = $request->validated();

        // Call the service to update the restaurant data
        $restaurant = $this->restaurantService->updateRestaurantdata($restaurant, $validateddata);

        // Return success response with the updated restaurant
        return self::success($restaurant, 'Restaurant data updated');
    }

    /**
     * Remove the specified restaurant resource from storage.
     *
     * @param Restaurant $restaurant The restaurant to be deleted.
     * @return \Illuminate\Http\JsonResponse A success message indicating that the restaurant data has been deleted.
     */
    public function destroy(Restaurant $restaurant)
    {
        // Call the service to delete the restaurant data
        $this->restaurantService->DeleteRestaurantdata($restaurant);

        // Return success response after deletion
        return self::success(null, 'Restaurant data deleted');
    }

    /**
     * Delete an email associated with the restaurant by its ID.
     *
     * @param int $id The email ID to be deleted.
     * @return \Illuminate\Http\JsonResponse A success message indicating that the email was deleted.
     */
    public function deleteEmail($id)
    {
        // Call the service to delete the email
        $this->restaurantService->deleteEmail($id);

        // Return success response after deletion
        return self::success(null, 'Email deleted');
    }

    /**
     * Delete a phone number associated with the restaurant by its ID.
     *
     * @param int $id The phone number ID to be deleted.
     * @return \Illuminate\Http\JsonResponse A success message indicating that the phone number was deleted.
     */
    public function deletePhoneNumber($id)
    {
        // Call the service to delete the phone number
        $this->restaurantService->deletePhoneNumber($id);

        // Return success response after deletion
        return self::success(null, 'Phone number deleted');
    }



    /**
     * Soft delete an image associated with a restaurant by its ID.
     *
     * @param string $restaurantId The restaurant ID to which the image belongs.
     * @param string $imageId The image ID to be soft deleted.
     * @return \Illuminate\Http\JsonResponse A success message indicating that the image was soft deleted.
     */
    public function softdeletImage($restaurantId, $imageId)
    {
        // Call the service to soft delete the image
        $this->restaurantService->softDeleteRestaurantImage($imageId, $restaurantId);

        // Return success response after soft delete
        return self::success(null, 'Restaurant Image soft deleted');
    }

    /**
     * Show all soft-deleted images for a restaurant.
     *
     * @return \Illuminate\Http\JsonResponse A list of all soft-deleted images.
     */
    public function showDeletedImage()
    {
        // Get all deleted images from the service
        $images = $this->restaurantService->getDeletedImages();

        // Return success response with the deleted images
        return self::success($images, ' Restaurant Images deleted.', 200);
    }

    /**
     * Restore a soft-deleted image associated with a restaurant.
     *
     * @param string $restaurantId The ID of the restaurant.
     * @param string $imageId The ID of the image to restore.
     * @return \Illuminate\Http\JsonResponse A success message indicating that the image was restored.
     */
    public function restoreImage($restaurantId, $imageId)
    {
        // Call the service to restore the soft-deleted image
        $this->restaurantService->restoreRestaurantImage($imageId, $restaurantId);

        // Return success response after restoration
        return self::success(null, 'Restaurant Image restored');
    }

    /**
     * Permanently delete an image associated with a restaurant.
     *
     * @param string $restaurantId The ID of the restaurant.
     * @param string $imageId The ID of the image to permanently delete.
     * @return \Illuminate\Http\JsonResponse A success message indicating that the image was permanently deleted.
     */
    public function deletImage($restaurantId, $imageId)
    {
        // Call the service to permanently delete the image
        $this->restaurantService->permanentlyDeleteImage($imageId, $restaurantId);
        // Return success response after permanent deletion
        return self::success(null, 'Restaurant Image permanently deleted');
    }


}
