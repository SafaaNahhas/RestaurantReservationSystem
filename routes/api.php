<?php


use App\Enums\RoleUser;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Models\User;
use App\Http\Controllers\Api\Reservation\RatingController;
use App\Http\Controllers\Api\Reservation\DishController;
use App\Http\Controllers\Api\Reservation\FoodCategoryController;
use App\Http\Controllers\Api\Reservation\TableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Reservation\ReservationController;
use App\Http\Controllers\Api\Reservation\EventController;
// use app\Http\Controllers\Api\Reservation\DepartmentController;

use App\Http\Controllers\Api\Reservation\DepartmentController;



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




    Route::post('categories', [FoodCategoryController::class, 'store']);
    Route::put('category/{category}', [FoodCategoryController::class, 'update']);
    Route::delete('category/{category}', [FoodCategoryController::class, 'destroy']);
    Route::get('categories', [FoodCategoryController::class, 'index']);
    Route::get('category/{category}', [FoodCategoryController::class, 'show']);

    Route::post('dishes', [DishController::class, 'store']);
    Route::put('dish/{dish}', [DishController::class, 'update']);
    Route::delete('dish/{dish}', [DishController::class, 'destroy']);
    Route::get('dishes', [DishController::class, 'index']);
    Route::get('dish/{dish}', [DishController::class, 'show']);

    Route::prefix('admin')->group(function () {
        Route::apiResource('departments.tables', TableController::class);
        Route::get('departments/{department}/allDeletedTables', [TableController::class, 'allDeletedTables']);
        Route::post('departments/{department}/tables/{table}/restore', [TableController::class, 'restoreTable']);
        Route::delete('departments/{department}/tables/{table}/forceDelete', [TableController::class, 'forceDeleteTable']);


        // Route::apiResource('departments', DepartmentController::class);
        // use App\Http\Controllers\Api\Reservation\DepartmentController;
        // Route::apiResource('event', EventController::class);

        Route::get('event/showDeleted', [EventController::class, 'showDeleted']);
        Route::put('event/{id}/restore', [EventController::class, 'restoreDeleted']);
        Route::delete('event/{id}/delete', [EventController::class, 'forceDeleted']);
        Route::apiResource('event', EventController::class);

        Route::get('department/showDeleted', [DepartmentController::class, 'showDeleted']);
        Route::put('department/{id}/restore', [DepartmentController::class, 'restoreDeleted']);
        Route::delete('department/{id}/delete', [DepartmentController::class, 'forceDeleted']);
        Route::apiResource('department', DepartmentController::class);

        // Route::prefix('customer')->group(function () {
        //     Route::apiResource('departments.tables', TableController::class)->only(['index', 'show']);
        // });
        Route::prefix('v1')->group(function () {
            Route::apiResource('users', UserController::class);
            Route::post('users/restore/{id}', [UserController::class, 'restore']);
            Route::get('show-deleted-users', [UserController::class, 'trashedUsers']);
            Route::delete('force-delete/{id}', [UserController::class, 'forceDelete']);
        });
    });


    // Route::prefix('auth/v1')->group(function (){
    //     Route::post('register', [AuthController::class, 'register']);
    //     Route::post('login', [AuthController::class, 'login']);
    //     Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    //     Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    // });



});
Route::apiResource('rating', RatingController::class);
Route::get('/rating_deleted', [RatingController::class, 'getDeletedRatings']); // Get deleted ratings
Route::patch('rating/restore/{id}', [RatingController::class, 'restoreRating']); // Restore a deleted rating
Route::delete('rating/force-delete/{id}', [RatingController::class, 'forceDeleteRating']); // Permanently delete rating
