<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Food\DishController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Event\EventController;


use App\Http\Controllers\Api\Rating\RatingController;
use App\Http\Controllers\Api\Email\EmailLogController;
// use App\Http\Controllers\NotificationSettingsController;
use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Api\Favorite\FavoriteController;
use App\Http\Controllers\Api\Food\FoodCategoryController;
use App\Http\Controllers\Api\Reservation\TableController;
use App\Http\Controllers\Api\Auth\ForgetPasswordController;
use App\Http\Controllers\Api\Emergency\EmergencyController;
use App\Http\Controllers\Api\Restaurant\DepartmentController;
use App\Http\Controllers\Api\Restaurant\RestaurantController;
use App\Http\Controllers\Api\RoleAndPermission\RoleController;
use App\Http\Controllers\Api\Reservation\ReservationController;
use App\Http\Controllers\Api\RoleAndPermission\PermissionController;
use App\Http\Controllers\Api\Auth\NotificationSettingsController;
use App\Http\Controllers\Api\Notification\NotificationLogController;

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
    Route::get('/tables-with-reservations', [ReservationController::class, 'getAllTablesWithReservations']);
    Route::delete('reservations/{id}/soft-delete', [ReservationController::class, 'softDeleteReservation']);
    Route::delete('reservations/{id}/force-delete', [ReservationController::class, 'forceDeleteReservation']);
    Route::post('reservations/{id}/restore', [ReservationController::class, 'restoreReservation']);
    Route::get('reservations/get-soft-deleted', [ReservationController::class, 'getSoftDeletedReservations']);
    Route::get('/reservations/manager/{managerId}', [ReservationController::class, 'getReservationsByManager']);
    Route::get('/reservations/most-frequent-user', [ReservationController::class, 'getMostFrequentUser']);
    Route::get('user/reservations/in_service', [ReservationController::class, 'getInServiceReservations']);
});



Route::post('/changePassword', [ForgetPasswordController::class, 'changePassword']);
Route::post('/checkEmail', [ForgetPasswordController::class, 'checkEmail']);
Route::post('/checkCode', [ForgetPasswordController::class, 'checkCode']);

//  ********* Rating Routes    *****************************
Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('ratings', RatingController::class)->except(['index']);
    Route::get('/rating_deleted', [RatingController::class, 'getDeletedRatings']); // Get deleted ratings
    Route::patch('rating/restore/{id}', [RatingController::class, 'restoreRating']); // Restore a deleted rating
    Route::delete('rating/force-delete/{id}', [RatingController::class, 'forceDeleteRating']); // Permanently delete rating
});

// ********* Category Routes  *********************************
Route::middleware(['auth:api', 'role:Admin'])->group(function () {
    Route::post('categories', [FoodCategoryController::class, 'store']);
    Route::put('category/{category}', [FoodCategoryController::class, 'update']);
    Route::delete('category/{category}', [FoodCategoryController::class, 'destroy']);
    Route::get('categories', [FoodCategoryController::class, 'index']);
    Route::get('category/{category}', [FoodCategoryController::class, 'show']);
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

// ***********  NotificationLog Routes **********************
Route::middleware(['auth:api'])->group(function () {
    // Define API resource routes for notification log
    Route::get('notificationLogs', [NotificationLogController::class, 'index']);
    // Define a route for soft deleting notification logs
    Route::get('notificationLogs/{notificationlogs}', [NotificationLogController::class, 'show']);
    // Define a route for soft deleting notification logs
    Route::delete('softDeleteNotificationLogs', [NotificationLogController::class, 'deleteNotificationLogs']);
    // Define a route for retrieving deleted notification logs
    Route::get('getDeletedNotificationLogs', [NotificationLogController::class, 'getDeletedNotificationLogs']);
    // Define a route for permanently deleting a soft-deleted notification log
    Route::delete('permanentlyDeleteNotificationLog/{notificationlogs}', [NotificationLogController::class, 'permanentlyDeleteNotificationLog']);
    // Define a route for restoring a soft-deleted notification log
    Route::post('restore/{notificationlogs}', [NotificationLogController::class, 'restoreNotificationLog']);
});


// ********* Departments Routes ***********************************
// Publicly accessible for all authenticated roles
Route::middleware(['auth:api', 'role:Admin'])->group(function () {
    Route::put('department/{id}/restore', [DepartmentController::class, 'restoreDeleted']);
    Route::delete('department/{id}/delete', [DepartmentController::class, 'forceDeleted']);
    Route::get('department/alldelet', [DepartmentController::class, 'alldelet']);
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
Route::middleware(['auth:api'])->group(function () {
    Route::get('department', [DepartmentController::class, 'index']); // Get all departments
    Route::get('department/{id}', [DepartmentController::class, 'show']); // Get a specific department
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
    Route::get('/all_favorites', [FavoriteController::class, 'getAllFavorites']);
    Route::post('/favorites', [FavoriteController::class, 'addToFavorites']);
    Route::get('/favorites', [FavoriteController::class, 'getFavorites']);
    Route::delete('/favorites', [FavoriteController::class, 'removeFromFavorites']);
    Route::get('/favorite_deleted', [FavoriteController::class, 'getDeletedFavorite']); // Get deleted favorites
    Route::patch('favorite/restore/{id}', [FavoriteController::class, 'restorefavorite']); // Restore a deleted favorite
    Route::delete('favorite/force-delete/{id}', [FavoriteController::class, 'forceDeletefavorite']); // Permanently delete favorite});
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

// ********** Emergency Routes *****************************

Route::middleware(['auth:api', 'role:Admin'])->group(function () {
    Route::apiResource('emergencies', EmergencyController::class);
});

// *******  Roles Routes *******************************
Route::middleware(['auth:api', 'role:Admin'])->group(function () {
    Route::apiResource('roles', RoleController::class);
    Route::post('/roles/{role}/addPermissions', [RoleController::class, 'addPermissionToRole']);
    Route::post('/roles/{role}/removePermission', [RoleController::class, 'removePermissionFromRole']);
});
// *******  Permissions Routes *******************************

Route::middleware(['auth:api', 'role:Admin'])->group(function () {
    Route::apiResource('permissions', PermissionController::class);
});

Route::middleware(['auth:api'])->group(function () {
    Route::post('notificationSettings', [NotificationSettingsController::class, 'store']);
    Route::put('notificationSettings', [NotificationSettingsController::class, 'update']);
    Route::get('checkIfNotificationSettingsExsits', [NotificationSettingsController::class, 'checkIfNotificationSettingsExsits']);
    Route::post('resetNotificationSettings', [NotificationSettingsController::class, 'resetNotificationSettings']);
});
Route::apiResource('notificationSettings', NotificationSettingsController::class)->only('store', 'update');
//*********** payment route**********************************

Route::post('/process-payment', [PaymentController::class, 'processPayment']);
Route::get('user/reservations/in_service', [ReservationController::class, 'getInServiceReservations'])->middleware('auth');
