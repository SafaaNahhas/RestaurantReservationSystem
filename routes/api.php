<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Reservation\DishController;
use App\Http\Controllers\Api\Reservation\FoodCategoryController;
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

Route::post('register',[AuthController::class,'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');


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