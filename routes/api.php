<?php

use App\Enums\RoleUser;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Models\User;
use App\Http\Controllers\Api\Reservation\RatingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Reservation\ReservationController;

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


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('reservations', [ReservationController::class, 'storeReservation']);
    Route::post('/reservations/{id}/confirm', [ReservationController::class, 'confirmReservation']);
    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancelReservation']);
    Route::post('/reservations/{id}/start-service', [ReservationController::class, 'startService']);
    Route::post('/reservations/{id}/complete-service', [ReservationController::class, 'completeService']);
    Route::post('reservations/auto-cancel', [ReservationController::class, 'cancelUnconfirmedReservations']);
    Route::delete('reservations/{id}/hard-delete', [ReservationController::class, 'hardDeleteReservation']);
    Route::apiResource('rating', RatingController::class);
    Route::get('test', [RatingController::class, 'test']);
    Route::get('/rating_deleted', [RatingController::class, 'getDeletedRatings']); // Get deleted ratings
    Route::patch('rating/restore/{id}', [RatingController::class, 'restoreRating']); // Restore a deleted rating
    Route::delete('rating/force-delete/{id}', [RatingController::class, 'forceDeleteRating']); // Permanently delete rating
});

