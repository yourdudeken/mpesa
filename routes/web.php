<?php

use App\Http\Controllers\MerchantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/


// Authentication Routes (Public)
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])
    ->middleware('throttle.login')
    ->name('login.submit');
Route::get('/signup', [App\Http\Controllers\AuthController::class, 'showSignup'])->name('signup');
Route::post('/signup', [App\Http\Controllers\AuthController::class, 'signup'])
    ->middleware('throttle.login')
    ->name('signup.submit');
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Merchant Management Routes - Protected by Session Auth
Route::middleware(['merchant.auth'])->group(function () {
    Route::get('/', [MerchantController::class, 'index'])->name('merchants.index');
    Route::get('/merchants', [MerchantController::class, 'list'])->name('merchants.list');
    Route::post('/merchants', [MerchantController::class, 'store'])->name('merchants.store');
    Route::get('/merchants/{id}/edit', [MerchantController::class, 'edit'])->name('merchants.edit');
    Route::put('/merchants/{id}', [MerchantController::class, 'update'])->name('merchants.update');
    Route::post('/merchants/{id}/regenerate-key', [MerchantController::class, 'regenerateApiKey'])->name('merchants.regenerate');
    Route::post('/merchants/{id}/toggle-status', [MerchantController::class, 'toggleStatus'])->name('merchants.toggle');
    Route::delete('/merchants/{id}', [MerchantController::class, 'destroy'])->name('merchants.destroy');
});
