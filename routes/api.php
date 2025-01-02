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
use App\Http\Controllers\Api\Reservation\ReservationController;
use App\Http\Controllers\Api\Reservation\FoodCategoryController;

// ***********  Auth Routes ****************************

// Route::middleware(['security'])->group(function (){
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('ratings', [RatingController::class, 'index']);

// Define routes with 'auth:api' and 'role:Admin' middleware
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
