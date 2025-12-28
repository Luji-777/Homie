<?php

use Illuminate\Http\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/cities', [CityController::class, 'cities']);
Route::get('/areas', [CityController::class, 'areas']);


Route::post('/register', [UserController::class, 'register']);
Route::post('/verifyOtp', [UserController::class, 'verifyOtp']);
Route::post('/resendOtp', [UserController::class, 'resendOtp']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');


Route::apiResource('apartments', ApartmentController::class)->middleware('auth:sanctum');
// Route::get('apartments/{id}', [ApartmentController::class, 'show']);
Route::get('/filter', [ApartmentController::class, 'filter']);
Route::get('/approvedApartments', [ApartmentController::class, 'approvedApartments']);


Route::post('/createReview', [ReviewController::class, 'store']);
Route::get('/apartmentReview/{id}', [ReviewController::class, 'show']);
Route::delete('/deleteReview/{id}', [ReviewController::class, 'destroy']);

Route::get('/myFavorites', [FavoriteController::class, 'myFavorites']);
Route::post('/addFavorite', [FavoriteController::class, 'addFavorite']);
Route::delete('/removeFavorite', [FavoriteController::class, 'removeFavorite']);
Route::get('/isFavorite/{id}', [FavoriteController::class, 'isFavorite']);

Route::get('/cities', [CityController::class, 'cities']);
Route::get('/areas', [CityController::class, 'areas']);



Route::post('/apartments/{apartment}/book', [BookingController::class, 'store'])->middleware('auth:sanctum');


Route::get('/apartments/{id}/booked-dates', [BookingController::class, 'getBookedDates'])
    ->middleware('auth:sanctum'); // أو أي middleware مناسب

Route::post('/bookings/{bookingId}/owner-response', [BookingController::class, 'handleOwnerResponse'])
    ->middleware('auth:sanctum') // أو auth:api حسب نظام الـ authentication عندك
    ->name('bookings.owner-response');
