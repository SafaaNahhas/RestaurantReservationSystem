<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Reservation\EventController;
// use app\Http\Controllers\Api\Reservation\DepartmentController;

use App\Http\Controllers\Api\Reservation\DepartmentController;

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
