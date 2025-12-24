<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;

Route::get('/', function () {
    return view('welcome');
    });


Route::get('/admin', [AdminDashboardController::class, 'index']);
Route::post('/admin/approve/{id}', [AdminDashboardController::class, 'approve']);
Route::delete('/admin/delete/{id}', [AdminDashboardController::class, 'delete']);