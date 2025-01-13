<?php

namespace App\Http\Controllers\Api\Favorite;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Services\Favorite\FavoriteService;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\Favorite\AddToFavoritesRequest;
use App\Http\Resources\Favorite\FavoriteResource;

class FavoriteController extends Controller
{
    protected $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    /**
     *  View a list of favorites with the possibility of filtering using the type.
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing query parameters.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllFavorites(Request $request)
    {
        $this->authorize('showAllFavorite', Favorite::class);

        $type = $request->input('type');

        $perPage = $request->input('per_page', 10);

        // Fetching data with filtering and pagination application
        $favorites = Favorite::when($type, function ($query, $type) {
            return $query->byType($type);
        })->paginate($perPage);

        return $this->paginated($favorites, FavoriteResource::class, 'Favorite fetched successfully', 200);
    }



    //************************************************************************* */

    /**
     * Store a newly created favorite (table or food category) in storage.
     * @param AddToFavoritesRequest $request
     *  @return \Illuminate\Http\JsonResponse
     */
    public function addToFavorites(AddToFavoritesRequest $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();
        $data = $this->favoriteService->addToFavorites($user, $request->type, $request->id);



        if ($data['status'] === 'exists') {
            Cache::forget('favorite_all');

            return $this->error(null, $data['message'], 409);
        }

        if ($data['status'] === 'error') {
            Cache::forget('favorite_all');

            return $this->error(null, $data['message'], 500);
        }

        return $this->success(null, $data['message'], 201);
    }



    //************************************************************ */

    /**
     * Display a listing of the favorite.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFavorites(): JsonResponse
    {

        $user = JWTAuth::parseToken()->authenticate();
        $favorites = $this->favoriteService->getFavorites($user);

        return response()->json($favorites);
    }

    /**
     * remove a favorite (table or food category) from the storage.
     * @param AddToFavoritesRequest $request
     *  @return \Illuminate\Http\JsonResponse
     */
    public function removeFromFavorites(AddToFavoritesRequest $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();
        $data = $this->favoriteService->removeFromFavorites($user, $request->type, $request->id);

        if ($data['status'] === 'exists') {
            Cache::forget('favorite_all');

            return $this->error(null, $data['message'], 404);
        }

        if ($data['status'] === 'error') {
            Cache::forget('favorite_all');

            return $this->error(null, $data['message'], 500);
        }

        return $this->success(null, $data['message'], 200);
    }


    //************************************************************************* */

    /**
     * Get deleted favorite.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeletedFavorite(Request $request)
    {
        $this->authorize('getDeleting', Favorite::class);
        $perPage = $request->input('per_page', 10);
        $deletedfavorite = $this->favoriteService->get_deleted_favorites($perPage);
        return $this->success($deletedfavorite);
    }

    //************************************************************************* */

      /**
     * Restore a trashed (soft deleted) resource by its ID.
     */
    public function restorefavorite($id)
    {
        $this->authorize('restore', Favorite::class);
        $favorite = $this->favoriteService->restore_favorite($id);
        return $this->success("favorite restored Successfully");
    }

    //*********************************************************** */

   /**
     * Permanently delete a trashed (soft deleted) resource by its ID.
     */
    public function forceDeleteFavorite($favoriteId)
    {
        $this->authorize('forceDelete', Favorite::class);
        $this->favoriteService->force_delete_favorite($favoriteId);
        return $this->success(null, "FoodCategory deleted Permanently");
    }
}