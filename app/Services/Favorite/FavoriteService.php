<?php

namespace App\Services\Favorite;

use Exception;
use App\Models\Table;
use App\Models\Favorite;
use App\Models\FoodCategory;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Http\Resources\Favorite\FavoriteResource;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FavoriteService
{
    /**
     * Retrieve all favorites with optional type filter and pagination
     *
     * @param array $data
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllFavorites(array $data)
    {
        try {
            // جلب المفضلات بناءً على النوع إذا تم توفيره
            $favorites = Favorite::when(!empty($data['type']), function ($query) use ($data) {
                return $query->byType($data['type']);
            })->paginate($data['perPage']);

            return $favorites;
        } catch (Exception $e) {
            // تسجيل الخطأ وإرجاع استثناء HTTP
            Log::error('Error fetching favorite data: ' . $e->getMessage());
            throw new HttpException(500, 'Error fetching favorite data');
        }
    }


    //************************************************************ */

    /**
     * Add an item to the user's favorites
     *
     * @param $user
     * @param string $type
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function addToFavorites($user, $type, $id)
    {
        try {
            // Authenticate the user
            $user = JWTAuth::parseToken()->authenticate();

            // Determine the model based on the type
            $model = $type === 'tables' ? Table::class : FoodCategory::class;

            // Find the item by its ID
            $item = $model::findOrFail($id);

            // Check if the item is already in the user's favorites
            if ($user->favorites()->where('favorable_type', $model)->where('favorable_id', $id)->exists()) {
                throw new \Exception('Item already in favorites', 409);
            }

            // Create a new favorite record
            $data = $user->favorites()->create([
                'favorable_type' => $model,
                'favorable_id' => $item->id,
            ]);

            return $data;
        } catch (\Exception $e) {
            if ($e->getCode() === 409) {
                throw new HttpException(409, $e->getMessage());
            }
            Log::error('Error in favoriteService@addToFavorites: ' . $e->getMessage());
            throw new HttpException(500, 'An unexpected error occurred');
        }
    }

    //************************************************************ */

    /**
     * Fetch the list of favorites for a user
     *
     * @param $user
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getFavorites($user)
    {
        try {

            $favorites = $user->favorites()->with('favorable')->paginate(10);
            return $favorites;
        } catch (\Exception $e) {
            Log::error("Error in getting user's favorites: " . $e->getMessage());
            throw new HttpException(500, "Error in getting user's favorites");
        }
    }

    //************************************************************ */

    /**
     * Remove an item from the user's favorites
     *
     * @param $user
     * @param string $type
     * @param int $id
     * @return bool
     */
    public function removeFromFavorites($user, $type, $id)
    {
        try {
            // Authenticate the user
            $user = JWTAuth::parseToken()->authenticate();

            // Determine the model based on the type
            $model = $type === 'tables' ? Table::class : FoodCategory::class;

            // Find the favorite item
            $favorite = $user->favorites()
                ->where('favorable_type', $model)
                ->where('favorable_id', $id)
                ->first();

            if (!$favorite) {
                throw new HttpException(404, "Item Not Found");
            }

            // Delete the favorite item
            $favorite->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Error in favoriteService@removeFromFavorites: ' . $e->getMessage());
            throw new HttpException(500, 'An unexpected error occurred');
        }
    }

    //************************************************************ */

    /**
     * Retrieve all soft-deleted favorites
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_deleted_favorites()
    {
        try {
            // Fetch only soft-deleted favorites
            $deletedFavorite = Favorite::onlyTrashed()->get();
            return $deletedFavorite;
        } catch (\Exception $e) {
            Log::error('Error in favoriteService@get_deleted_favorites: ' . $e->getMessage());
            throw new HttpException(500, "Error in getting deleted favorites");
        }
    }

    //************************************************************ */

    /**
     * Restore a soft-deleted favorite
     *
     * @param int $favoriteId
     * @return bool
     */
    public function restore_favorite($favoriteId)
    {
        try {
            // Find the soft-deleted favorite
            $favorite = Favorite::onlyTrashed()->findOrFail($favoriteId);

            // Restore the favorite
            $favorite->restore();

            return true;
        } catch (\Exception $e) {
            Log::error('Error in favoriteService@restore_favorite: ' . $e->getMessage());
            throw new HttpException(500, "Failed to restore favorite");
        }
    }

    //************************************************************ */

    /**
     * Permanently delete a soft-deleted favorite
     *
     * @param int $favoriteId
     * @return bool
     */
    public function permanently_delete_favorite($favoriteId)
    {
        try {
            // Find the soft-deleted favorite
            $favorite = Favorite::onlyTrashed()->findOrFail($favoriteId);

            // Permanently delete the favorite
            $favorite->forceDelete();

            return true;
        } catch (\Exception $e) {
            Log::error('Error in favoriteService@force_delete_favorite: ' . $e->getMessage());
            throw new HttpException(500, "Error in force_delete_favorite:");
        }
    }
}
