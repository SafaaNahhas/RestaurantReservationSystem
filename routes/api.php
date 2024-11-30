<?php

use App\Http\Controllers\Api\Reservation\RatingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::apiResource('rating', RatingController::class);
Route::get('test', [RatingController::class, 'test']);



Route::get('/rating_deleted', [RatingController::class, 'getDeletedRatings']); // Get deleted ratings
Route::patch('rating/restore/{id}', [RatingController::class, 'restoreRating']); // Restore a deleted rating
Route::delete('rating/force-delete/{id}', [RatingController::class, 'forceDeleteRating']); // Permanently delete rating
