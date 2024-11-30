<?php

use App\Enums\RoleUser;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Models\User;
use App\Http\Controllers\Api\Reservation\RatingController;
use App\Http\Controllers\Api\Reservation\DishController;
use App\Http\Controllers\Api\Reservation\FoodCategoryController;
use App\Http\Controllers\Api\Reservation\TableController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Reservation\ReservationController;
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
