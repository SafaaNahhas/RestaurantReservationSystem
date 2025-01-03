<?php


use App\Models\User;
use App\Enums\RoleUser;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgetPasswordController;

use App\Http\Controllers\Api\Reservation\RatingController;
use App\Http\Controllers\Api\Reservation\DishController;
use App\Http\Controllers\Api\Reservation\FoodCategoryController;
use App\Http\Controllers\Api\Reservation\TableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Reservation\EventController;
use App\Http\Controllers\Api\Reservation\FavoriteController;
use App\Http\Controllers\Api\Reservation\DepartmentController;

use App\Http\Controllers\Api\Reservation\ReservationController;
use App\Http\Controllers\Api\Restaurant\RestaurantController;
use App\Models\Restaurant;

use App\Http\Controllers\Api\Reservation\RoleController;
use App\Http\Controllers\Api\Reservation\PermissionController;

// ***********  Auth Routes ****************************

// Route::middleware(['security'])->group(function (){
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('ratings', [RatingController::class, 'index']);

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
    Route::post('/reservations/{id}/confirm', [ReservationController::class, 'confirmReservation']);
    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancelReservation']);
    Route::post('/reservations/{id}/start-service', [ReservationController::class, 'startService']);
    Route::post('/reservations/{id}/complete-service', [ReservationController::class, 'completeService']);
    Route::post('reservations/auto-cancel', [ReservationController::class, 'cancelUnconfirmedReservations']);
    Route::delete('reservations/{id}/hard-delete', [ReservationController::class, 'hardDeleteReservation']);
    Route::get('/tables-with-reservations', [ReservationController::class, 'getAllTablesWithReservations']);
});

//  ********* Rating Routes    *****************************
Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('ratings', RatingController::class);
    // Route::get('/rating_deleted', [RatingController::class, 'getDeletedRatings']); // Get deleted ratings
    // Route::patch('rating/restore/{id}', [RatingController::class, 'restoreRating']); // Restore a deleted rating
    // Route::delete('rating/force-delete/{id}', [RatingController::class, 'forceDeleteRating']); // Permanently delete rating
});

// ********* Category Routes  *********************************
Route::middleware(['auth:api', 'role:Admin'])->group(function () {
    Route::post('categories', [FoodCategoryController::class, 'store']);
    Route::put('category/{category}', [FoodCategoryController::class, 'update']);
    Route::delete('category/{category}', [FoodCategoryController::class, 'destroy']);
    Route::get('categories', [FoodCategoryController::class, 'index']);
    Route::get('category/{category}', [FoodCategoryController::class, 'show']);
});


// *******  Roles Routes *******************************

Route::middleware( ['auth:api', 'role:Admin'])->group(function () {
    Route::apiResource('roles', RoleController::class);
    Route::post('/roles/{role}/addPermissions', [RoleController::class, 'addPermissionToRole']);
    Route::post('/roles/{role}/removePermission', [RoleController::class, 'removePermissionFromRole']);
});

// *******  Permissions Routes *******************************

Route::middleware(  ['auth:api', 'role:Admin'])->group(function () {
    Route::apiResource('permissions', PermissionController::class);
});



// *******  Dishes Routes *******************************
Route::middleware(['auth:api', 'role:Admin'])->group(function () {
    Route::post('dishes', [DishController::class, 'store']);
    Route::put('dish/{dish}', [DishController::class, 'update']);
    Route::delete('dish/{dish}', [DishController::class, 'destroy']);
    Route::get('dishes/showDeleted', [DishController::class, 'trashed']);
    Route::put('dishes/{id}/restore', [DishController::class, 'restore']);
    Route::delete('dishes/{id}/delete', [DishController::class, 'forceDelete']);
    // soft delete
    Route::delete('dishes/{dishId}/imageSoftDelet/{imageId}', [DishController::class, 'softDeleteImage']);
    // restore
    Route::post('dishes/{dishId}/imageRestore/{imageId}', [DishController::class, 'restoreImage']);
    // permanent delete
    Route::delete('dishes/{dishId}/imageDelete/{imageId}', [DishController::class, 'deleteImage']);
    // show deleted image
    Route::get('dishes/showDeletedImage', [DishController::class, 'showDeletedImage']);
});
Route::get('dishes', [DishController::class, 'index']);
Route::get('dish/{dish}', [DishController::class, 'show']);
// ***********  Departments Tables Routes **********************
Route::middleware(['auth:api', 'role:Admin'])->group(function () {
    Route::apiResource('departments.tables', TableController::class);
    Route::get('departments/{department}/allDeletedTables', [TableController::class, 'allDeletedTables']);
    Route::post('departments/{department}/tables/{table}/restore', [TableController::class, 'restoreTable']);
    Route::delete('departments/{department}/tables/{table}/forceDelete', [TableController::class, 'forceDeleteTable']);
});


// ********* Departments Routes ***********************************
// Publicly accessible for all authenticated roles
Route::middleware(['auth:api'])->group(function () {
    Route::get('department', [DepartmentController::class, 'index']); // Get all departments
    Route::get('department/{id}', [DepartmentController::class, 'show']); // Get a specific department
});
Route::middleware(['auth:api', 'role:Admin'])->group(function () {
    Route::get('department/showDeleted', [DepartmentController::class, 'showDeleted']);
    Route::put('department/{id}/restore', [DepartmentController::class, 'restoreDeleted']);
    Route::delete('department/{id}/delete', [DepartmentController::class, 'forceDeleted']);
    Route::apiResource('department', DepartmentController::class)->except(['index', 'show']);
    // soft delete
    Route::delete('department/{departmentId}/imageSoftDelet/{imageId}', [DepartmentController::class, 'softdeletImage']);
    // restore
    Route::post('department/{departmentId}/imageRestore/{imageId}', [DepartmentController::class, 'restoreImage']);
    // permanent delete
    Route::delete('department/{departmentId}/imageDdelet/{imageId}', [DepartmentController::class, 'deletImage']);
    // show deleted image
    Route::get('departments/showDeletedImage', [DepartmentController::class, 'showDeletedImage']);
});

// ************ Event Routes *************************************
Route::middleware(['auth:api', 'role:Admin'])->group(function () {
    Route::get('event/showDeleted', [EventController::class, 'showDeleted']);
    Route::put('event/{id}/restore', [EventController::class, 'restoreDeleted']);
    Route::delete('event/{id}/delete', [EventController::class, 'forceDeleted']);
    Route::apiResource('event', EventController::class)->except(['index', 'show']);
});
Route::middleware(['auth:api'])->group(function () {
    Route::get('event', [EventController::class, 'index']); // Get all departments
    Route::get('event/{id}', [EventController::class, 'show']); // Get a specific department
});

//   *********** Restaurant Routes ***********************
Route::middleware(['auth:api', 'role:Admin'])->group(function () {
    // soft delete
    Route::delete('restaurant/{restaurantId}/imageSoftDelet/{imageId}', [RestaurantController::class, 'softdeletImage']);
    // restore
    Route::post('restaurant/{restaurantId}/imageRestore/{imageId}', [RestaurantController::class, 'restoreImage']);
    // permanent delete
    Route::delete('restaurant/{restaurantId}/imageDdelet/{imageId}', [RestaurantController::class, 'deletImage']);
    // show deleted image
    Route::get('restaurant/showDeletedImage', [RestaurantController::class, 'showDeletedImage']);
    //restaurant
    Route::delete('restaurant/email/{id}', [RestaurantController::class, 'deleteEmail']);
    Route::delete('restaurant/phone-number/{id}', [RestaurantController::class, 'deletePhoneNumber']);
    Route::apiResource('restaurant', RestaurantController::class)->except(['index', 'show']);
});

Route::get('restaurant', [RestaurantController::class, 'index']);
Route::get('restaurant/{id}', [RestaurantController::class, 'show']);

// **********  Favorites Routes ***********************


Route::middleware('auth:api')->group(function () {
    Route::post('/favorites', [FavoriteController::class, 'addToFavorites']);
    Route::get('/favorites', [FavoriteController::class, 'getFavorites']);
    Route::delete('/favorites', [FavoriteController::class, 'removeFromFavorites']);
});

// *********  Users Routes  **************************************
Route::middleware(['auth:api', 'role:Admin'])->group(function () {
    Route::apiResource('users', UserController::class)->except('update');
    Route::post('users/restore/{id}', [UserController::class, 'restore']);
    Route::get('show-deleted-users', [UserController::class, 'trashedUsers']);
    Route::delete('force-delete/{id}', [UserController::class, 'forceDelete']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::put('users/{id}', [UserController::class, 'update']);
});
