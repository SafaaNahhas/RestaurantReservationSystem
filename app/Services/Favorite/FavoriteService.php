<?php

namespace App\Services\Favorite;


use App\Models\Table;
use App\Models\Favorite;
use App\Models\FoodCategory;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Favorite\FavoriteResource;
use Exception;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            // check if user already add the item to his favorite list 
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


    //*************************************************************** */

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

//************************************************************************ */

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

//******************************************************************* */

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
        } catch (Exception $e) {
            Log::error("error in display list of trashed FoodCategory" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }


//********************************************************************* */

     /**
     * Restore a trashed (soft deleted) resource by its ID.
     *
     * @param  int  $id  The ID of the trashed Favourite to be restored.
     * @return \App\Models\Favorite
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the Task with the given ID is not found.
     * @throws \Exception If there is an error during the restore process.
     */
    public function restore_favorite($id)
    {
        try {
            $favorite = Favorite::onlyTrashed()->findOrFail($id);
            $favorite->restore();
            return $favorite;
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
            Log::error("error in restore a Favorite" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }



//********************************************************************** */

   /**
     * Permanently delete a trashed (soft deleted) resource by its ID.
     *
     * @param  int  $id  The ID of the trashed Task to be permanently deleted.
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the Task with the given ID is not found.
     * @throws \Exception If there is an error during the force delete process.
     */
    public function force_delete_favorite($favoriteId)
    {
        try {
            $favorite = Favorite::onlyTrashed()->findOrFail($favoriteId);
            $favorite->forceDelete();
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
            Log::error("error  in forceDelete FoodCategory" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }
}
