<?php

namespace App\Http\Controllers\Api\Reservation;

use Illuminate\Http\Request;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\Favorite\AddToFavoritesRequest;

class FavoriteController extends Controller
{
    protected $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

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

    public function getFavorites(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();
        $favorites = $this->favoriteService->getFavorites($user);

        return response()->json($favorites);
    }

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

        return $this->success(null, $data['message'], 201);
    }
}
