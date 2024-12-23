<?php

namespace App\Services;


use App\Models\Table;
use App\Models\FoodCategory;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FavoriteService
{
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


    public function getFavorites($user)
    {
        return FavoriteResource::collection(
            $user->favorites()->with('favorable')->get()
        );
    }

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
}
