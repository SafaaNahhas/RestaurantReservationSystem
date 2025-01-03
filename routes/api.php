<?php


use App\Models\User;
use App\Enums\RoleUser;
use App\Models\Restaurant;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Reservation\DishController;
use App\Http\Controllers\Api\Reservation\EventController;
use App\Http\Controllers\Api\Reservation\TableController;
use App\Http\Controllers\Api\Reservation\RatingController;
use App\Http\Controllers\Api\Auth\ForgetPasswordController;
use App\Http\Controllers\Api\Reservation\EmailLogController;
use App\Http\Controllers\Api\Reservation\FavoriteController;

use App\Http\Controllers\Api\Restaurant\RestaurantController;
use App\Http\Controllers\Api\Reservation\DepartmentController;
use App\Http\Controllers\Api\Reservation\EmergencyController;
use App\Http\Controllers\Api\Reservation\ReservationController;


use App\Http\Controllers\Api\Reservation\RoleController;
use App\Http\Controllers\Api\Reservation\PermissionController;

// ***********  Auth Routes ****************************

// Route::middleware(['security'])->group(function (){
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('ratings', [RatingController::class, 'index']);
Route::post('ratings', [RatingController::class, 'store']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);
});

Route::post('/checkEmail', [ForgetPasswordController::class, 'checkEmail']);
Route::post('/checkCode', [ForgetPasswordController::class, 'checkCode']);
Route::post('/changePassword', [ForgetPasswordController::class, 'changePassword']);


// **********  Reservation Routes *************************
Route::middleware(['auth:api'])->group(function () {
    Route::post('reservations', [ReservationController::class, 'storeReservation']);
    Route::put('/reservations/{id}', [ReservationController::class, 'updateReservation']);
    Route::post('/reservations/{id}/confirm', [ReservationController::class, 'confirmReservation']);
    Route::post('reservations/{reservationId}/reject', [ReservationController::class, 'rejectReservation']);
    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancelReservation']);
    Route::post('/reservations/{id}/start-service', [ReservationController::class, 'startService']);
    Route::post('/reservations/{id}/complete-service', [ReservationController::class, 'completeService']);
    Route::post('reservations/auto-cancel', [ReservationController::class, 'cancelUnconfirmedReservations']);
    // Route::delete('reservations/{id}/hard-delete', [ReservationController::class, 'hardDeleteReservation']);
    Route::get('/tables-with-reservations', [ReservationController::class, 'getAllTablesWithReservations']);
    Route::delete('reservations/{id}/soft-delete', [ReservationController::class, 'softDeleteReservation']);
    Route::delete('reservations/{id}/force-delete', [ReservationController::class, 'forceDeleteReservation']);
    Route::post('reservations/{id}/restore', [ReservationController::class, 'restoreReservation']);
    Route::get('reservations/get-soft-deleted', [ReservationController::class, 'getSoftDeletedReservations']);

    Route::post('/changePassword', [ForgetPasswordController::class, 'changePassword']);
});
Route::post('/checkEmail', [ForgetPasswordController::class, 'checkEmail']);
Route::post('/checkCode', [ForgetPasswordController::class, 'checkCode']);
//  ********* Rating Routes    *****************************
Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('ratings', RatingController::class)->except(['index', 'store']);
    Route::get('/rating_deleted', [RatingController::class, 'getDeletedRatings']); // Get deleted ratings
    Route::patch('rating/restore/{id}', [RatingController::class, 'restoreRating']); // Restore a deleted rating
    Route::delete('rating/force-delete/{id}', [RatingController::class, 'forceDeleteRating']); // Permanently delete rating
});

// ********* Category Routes  *********************************
Route::middleware(['auth:api', 'role:Admin'])->group(function () {
    // Define API resource routes for EmailLog
    Route::apiResource('emaillog', EmailLogController::class);

    // Define a route for soft deleting email logs
    Route::delete('softdeletemaillog', [EmailLogController::class, 'deleteEmailLogs']);

    // Define a route for retrieving deleted email logs
    Route::get('getemailogs', [EmailLogController::class, 'getDeletedEmailLogs']);

    // Define a route for permanently deleting a soft-deleted email log
    Route::delete('premanentdeletemaillog/', [EmailLogController::class, 'permanentlyDeleteEmailLog']);

    // Define a route for restoring a soft-deleted email log
    Route::post('restore/', [EmailLogController::class, 'restoreEmailLog']);
});


// ********** Emergency Routes *****************************

Route::middleware(['auth:api', 'role:Admin'])->group(function () {
    Route::apiResource('emergencies', EmergencyController::class);
});
