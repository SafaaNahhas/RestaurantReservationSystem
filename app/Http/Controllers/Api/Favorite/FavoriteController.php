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
    public function getAllFavorites(Request $request): JsonResponse
    {
        $this->authorize('showAllFavorite', Favorite::class);

        $data = [
            'type' => $request->input('type'),
            'perPage' => $request->input('per_page', 10)
        ];

        $favorites = $this->favoriteService->getAllFavorites($data);

        return $this->paginated($favorites, FavoriteResource::class, 'Favorites fetched successfully', 200);
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
        return self::success(null, "Added to favorites successfully");
    }

    //************************************************************ */

    /**
     * Display a listing of the favorite.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFavorites(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();
        $favorites = $this->favoriteService->getFavorites($user);
        return $this->paginated($favorites, FavoriteResource::class, 'user\'s favorites', 200);
    }


    //************************************************************************* */

    /**
     * Get deleted favorite.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeletedFavorite(Request $request)
    {
        $this->authorize('getDeleting', Favorite::class);

        $deletedFavorite = $this->favoriteService->get_deleted_favorites();
        return $this->success($deletedFavorite, 'Deleted favorite retrieved successfully.');
    }

    //************************************************************************* */
    /**
     * remove a favorite (table or food category) from the storage.
     * @param AddToFavoritesRequest $request
     *  @return \Illuminate\Http\JsonResponse
     */
    public function removeFromFavorites(AddToFavoritesRequest $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();
        $data = $this->favoriteService->removeFromFavorites($user, $request->type, $request->id);
        return $this->success(null, 'Item Removed successfully', 200);
    }

    //************************************************************************* */

    /**
     * Restore a deleted favorite.
     * @param int $favoriteId
     * @return \Illuminate\Http\JsonResponse
     */
    public function restorefavorite($favoriteId)
    {
        $this->authorize('restore', Favorite::class);
        $restored = $this->favoriteService->restore_favorite($favoriteId);
        return $this->success($restored, 'favorite restored successfully.');

    }

    //*********************************************************** */

    /**
     * Permanently delete a favorite.
     * @param int $favoriteId
     * @return \Illuminate\Http\JsonResponse
     */
    public function permanentlyDeleteFavorite($favoriteId)
    {
        $this->authorize('forceDelete', Favorite::class);
        $deleted = $this->favoriteService->permanently_delete_favorite($favoriteId);
        return $this->success($deleted, 'favorite permanently deleted.');

    }
}
