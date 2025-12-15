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

// Merchant Management Routes - Protected by HTTP Basic Auth
Route::middleware(['merchant.auth'])->group(function () {
    Route::get('/', [MerchantController::class, 'index'])->name('merchants.index');
    Route::get('/merchants', [MerchantController::class, 'list'])->name('merchants.list');
    Route::post('/merchants', [MerchantController::class, 'store'])->name('merchants.store');
    Route::post('/merchants/{id}/regenerate-key', [MerchantController::class, 'regenerateApiKey'])->name('merchants.regenerate');
    Route::post('/merchants/{id}/toggle-status', [MerchantController::class, 'toggleStatus'])->name('merchants.toggle');
    Route::delete('/merchants/{id}', [MerchantController::class, 'destroy'])->name('merchants.destroy');
});

