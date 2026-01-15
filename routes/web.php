<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;

Route::get('/', function () {
    return view('welcome');
});


// Route::get('/admin', [AdminDashboardController::class, 'index']);

Route::get('/admin', [AdminDashboardController::class, 'index'])
    ->name('admin.dashboard');


Route::post('/admin/approve/{id}', [AdminDashboardController::class, 'approve']);
Route::delete('/admin/delete/{id}', [AdminDashboardController::class, 'delete']);


Route::post('/admin/apartments/approve/{id}', [AdminDashboardController::class, 'approveApartment'])
    ->name('admin.approve.apartment');
Route::delete('/admin/apartments/delete/{id}', [AdminDashboardController::class, 'deleteApartment'])
    ->name('admin.delete.apartment');


// موافقة جميع المستخدمين المنتظرين
Route::post('/admin/approve-all-users', [AdminDashboardController::class, 'approveAllUsers'])->name('admin.approve.all.users');

// موافقة جميع الشقق المنتظرة
Route::post('/admin/approve-all-apartments', [AdminDashboardController::class, 'approveAllApartments'])->name('admin.approve.all.apartments');
Route::get('/admin/apartments/{id}', [AdminDashboardController::class, 'apartmentDetails'])
    ->name('admin.apartment.details');
