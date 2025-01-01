<?php

namespace App\Services;


use App\Models\Table;
use App\Models\Favorite;
use App\Models\FoodCategory;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FavoriteService
{

    /**
     * store the favorite
     * @param $user
     * @param array $type
     * @param array $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToFavorites($user, $type, $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $model = $type === 'tables' ? Table::class : FoodCategory::class;

            $item = $model::findOrFail($id);

            if ($user->favorites()->where('favorable_type', $model)->where('favorable_id', $id)->exists()) {
                return ['status' => 'exists', 'message' => 'Item already in favorites'];
            }

            $user->favorites()->create([
                'favorable_type' => $model,
                'favorable_id' => $item->id,
            ]);

            return ['status' => 'success', 'message' => 'Added to favorites successfully'];
        } catch (\Exception $e) {
            Log::error('Error in favoriteService@addToFavorites: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'An unexpected error occurred'];
        }
    }

    /**
     * fetch the list of favorite from the user
     * @param $user
     * @return \Illuminate\Http\JsonResponse
     */

    public function getFavorites($user)
    {
        return FavoriteResource::collection(
            $user->favorites()->with('favorable')->get()
        );
    }


    /**
     * remove the favorite recource
     * @param $user
     * @param array $type
     * @param array $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromFavorites($user, $type, $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $model = $type === 'tables' ? Table::class : FoodCategory::class;

            $favorite = $user->favorites()
                ->where('favorable_type', $model)
                ->where('favorable_id', $id)
                ->first();

            if (!$favorite) {
                return ['status' => 'exists', 'message' => 'Item Not Found'];
            }

            $favorite->delete();

            return ['status' => 'success', 'message' => 'Item Removed successfully'];
        } catch (\Exception $e) {
            Log::error('Error in favoriteService@removeFromFavorites: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'An unexpected error occurred'];
        }
    }

       /**
     * get all deleted favorite
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_deleted_favorites()
    {
        try {
            // Fetch only soft-deleted ratings
            $deletedFavorite = Favorite::onlyTrashed()->get();

            return $deletedFavorite;
        } catch (\Exception $e) {
            Log::error('Error in favoriteService@get_deleted_favorites: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * restore the favorite
     * @param  $favoriteId
     * @return \Illuminate\Http\JsonResponse
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
            return false;
        }
    }


    /**
     * force_delete the favorite
     * @param  $favoriteId
     * @return \Illuminate\Http\JsonResponse
     */
    public function force_delete_favorite($favoriteId)
    {
        try {
            // Find the soft-deleted favorite
            $favorite = Favorite::onlyTrashed()->findOrFail($favoriteId);

            // Permanently delete the favorite
            $favorite->forceDelete();

            return true;
        } catch (\Exception $e) {
            Log::error('Error in favoriteService@force_delete_favorite: ' . $e->getMessage());
            return false;
        }
    }
}
