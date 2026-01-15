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
use App\Http\Controllers\ProfileController;

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
Route::get('/filter', [ApartmentController::class, 'filter'])->middleware('auth:sanctum');
Route::get('/approvedApartments', [ApartmentController::class, 'approvedApartments'])->middleware('auth:sanctum');

///review//////////////////////////////
Route::post('/createReview', [ReviewController::class, 'store'])->middleware('auth:sanctum');
Route::get('/apartmentReview/{id}', [ReviewController::class, 'show']);
Route::delete('/deleteReview/{id}', [ReviewController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/topRated', [ReviewController::class, 'topRated'])->middleware('auth:sanctum');


///Favorite//////////////////////////////
Route::get('/myFavorites', [FavoriteController::class, 'myFavorites'])->middleware('auth:sanctum');
Route::post('/addFavorite', [FavoriteController::class, 'addFavorite'])->middleware('auth:sanctum');
Route::delete('/removeFavorite', [FavoriteController::class, 'removeFavorite'])->middleware('auth:sanctum');
Route::get('/isFavorite/{id}', [FavoriteController::class, 'isFavorite']);


///cities//////////////////////////////
Route::get('/cities', [CityController::class, 'cities']);
Route::get('/areas', [CityController::class, 'areas']);


///booking//////////////////////////////
Route::post('/apartments/{apartment}/book', [BookingController::class, 'store'])->middleware('auth:sanctum');
Route::get('/apartments/{id}/booked-dates', [BookingController::class, 'getBookedDates']);
Route::post('/bookings/{bookingId}/owner-response', [BookingController::class, 'handleOwnerResponse'])
    ->middleware('auth:sanctum')
    ->name('bookings.owner-response');
Route::get('/myBookings', [BookingController::class, 'myBookings'])->middleware('auth:sanctum');


// طلب إلغاء من المستأجر
Route::post('/bookings/{bookingId}/request-cancellation', [BookingController::class, 'requestCancellation'])
    ->middleware('auth:sanctum')
    ->name('bookings.request-cancellation');

// موافقة المالك على الإلغاء
Route::post('/bookings/{bookingId}/cancellation-response', [BookingController::class, 'handleCancellationResponse'])
    ->middleware('auth:sanctum')
    ->name('bookings.approve-cancellation');


// طلب تعديل من المستأجر
Route::post('/bookings/{bookingId}/request-modification', [BookingController::class, 'requestModification'])
    ->middleware('auth:sanctum')
    ->name('bookings.request-modification');

// موافقة المالك على التعديل
Route::post('/bookings/{bookingId}/modification-response', [BookingController::class, 'handleModificationResponse'])
    ->middleware('auth:sanctum')
    ->name('bookings.modification-response');



// عرض البروفايل
Route::get('/profile', [ProfileController::class, 'show'])->middleware('auth:sanctum');

// تعديل البروفايل
Route::put('/profile', [ProfileController::class, 'update'])->middleware('auth:sanctum');


// اختبار الترجمة
Route::get('/test-translation', function () {
    return response()->json([
        'message_en' => __('api.test'),   
        'message_current' => __('api.test'),  
        'current_locale' => app()->getLocale(),
    ]);
});



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});
