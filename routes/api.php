<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;



Route::prefix('auth/v1')->group(function (){
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
});


Route::prefix('v1')->group(function (){
    Route::apiResource('users', UserController::class);
    Route::post('users/restore/{id}', [UserController::class, 'restore']);
    Route::get('show-deleted-users', [UserController::class, 'trashedUsers']);
    Route::delete('force-delete/{id}', [UserController::class, 'forceDelete']);
});


