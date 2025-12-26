<?php

use Illuminate\Http\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [UserController::class, 'register']);
Route::post('/verifyOtp', [UserController::class, 'verifyOtp']);
Route::post('/resendOtp', [UserController::class, 'resendOtp']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');


Route::apiResource('apartments',ApartmentController::class)->middleware('auth:sanctum');

Route::get('/cities', [CityController::class, 'cities']);
Route::get('/areas', [CityController::class, 'areas']);

Route::post('/apartments/{apartment}/book', [BookingController::class, 'store'])->middleware('auth:sanctum');
