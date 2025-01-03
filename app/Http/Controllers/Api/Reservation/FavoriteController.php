<?php

namespace App\Http\Controllers\Api\Reservation;

use App\Models\Favorite;
use Illuminate\Http\Request;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\Favorite\AddToFavoritesRequest;
use Spatie\Permission\Exceptions\UnauthorizedException;

class FavoriteController extends Controller
{
    protected $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

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
            return $this->error(null, $data['message'], 409);
        }

        if ($data['status'] === 'error') {
            return $this->error(null, $data['message'], 500);
        }

        return $this->success(null, $data['message'], 201);
    }

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
            return $this->error(null, $data['message'], 404);
        }

        if ($data['status'] === 'error') {
            return $this->error(null, $data['message'], 500);
        }

        return $this->success(null, $data['message'], 200);
    }

     /**
     * Get deleted favorite.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeletedFavorite(Request $request)
    {
        $this->authorize('getDeleting', Favorite::class);

        $deletedfavorite = $this->favoriteService->get_deleted_favorites();

        if ($deletedfavorite) {
            return $this->success($deletedfavorite, 'Deleted favorite retrieved successfully.');
        } else {
            return $this->error('Failed to retrieve deleted favorite.', 500);
        }
    }


    /**
     * Restore a deleted favorite.
     * @param int $favoriteId
     * @return \Illuminate\Http\JsonResponse
     */
    public function restorefavorite($favoriteId)
    {
        $this->authorize('restore', Favorite::class);

        $restored = $this->favoriteService->restore_favorite($favoriteId);

        if ($restored) {
            return $this->success($restored,'favorite restored successfully.');
        } else {
            return $this->error('Failed to restore favorite.', 500);
        }
    }


    /**
     * Permanently delete a favorite.
     * @param int $favoriteId
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDeleteFavorite( $favoriteId)
    {
        $this->authorize('forceDelete', Favorite::class);

        
        $deleted = $this->favoriteService->force_delete_favorite($favoriteId);
        if ($deleted) {
            return $this->success($deleted,'favorite permanently deleted.');
        } else {
            return $this->error('Failed to permanently delete rating.', 500);
        }
    }
}