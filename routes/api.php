<?php

use Illuminate\Http\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\NotificationController;
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


Route::get('/apartments/{id}/booked-dates', [BookingController::class, 'getBookedDates']);

Route::post('/bookings/{bookingId}/owner-response', [BookingController::class, 'handleOwnerResponse'])
    ->middleware('auth:sanctum') // أو auth:api حسب نظام الـ authentication عندك
    ->name('bookings.owner-response');

Route::get('/myBookings', [BookingController::class, 'myBookings'])->middleware('auth:sanctum'); // بحاجة تجريب وتعديل الكود من عند لجين



// طلب إلغاء من المستأجر
Route::post('/bookings/{bookingId}/request-cancellation', [BookingController::class, 'requestCancellation'])
    ->name('bookings.request-cancellation');

// موافقة المالك على الإلغاء
Route::post('/bookings/{bookingId}/approve-cancellation', [BookingController::class, 'approveCancellation'])
    ->name('bookings.approve-cancellation');

// طلب تعديل من المستأجر
Route::post('/bookings/{bookingId}/request-modification', [BookingController::class, 'requestModification'])
    ->name('bookings.request-modification');

// موافقة المالك على التعديل
Route::post('/bookings/{bookingId}/approve-modification', [BookingController::class, 'approveModification'])
    ->name('bookings.approve-modification');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});
