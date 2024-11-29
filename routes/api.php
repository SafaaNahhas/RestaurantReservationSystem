<?php

use App\Http\Controllers\Api\Reservation\TableController;
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

Route::prefix('admin')->group(function () {
    Route::apiResource('departments.tables', TableController::class);
    Route::get('departments/{department}/allDeletedTables', [TableController::class, 'allDeletedTables']);
    Route::post('departments/{department}/tables/{table}/restore', [TableController::class, 'restoreTable']);
    Route::delete('departments/{department}/tables/{table}/forceDelete', [TableController::class, 'forceDeleteTable']);
});


Route::prefix('customer')->group(function () {
    Route::apiResource('departments.tables', TableController::class)->only(['index', 'show']);
});
