<?php

use App\Http\Controllers\Api\MpesaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| M-Pesa Gateway API Routes
| All routes are prefixed with /api
|
*/

// Health Check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'M-Pesa Gateway API',
        'environment' => config('app.env'),
        'mpesa_env' => config('mpesa.is_sandbox') ? 'sandbox' : 'production',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

// M-Pesa API Endpoints
Route::prefix('mpesa')->group(function () {
    
    // STK Push (Lipa Na M-Pesa Online)
    Route::post('/stk-push', [MpesaController::class, 'stkPush'])
        ->name('mpesa.stk-push');
    
    // STK Query
    Route::post('/stk-query', [MpesaController::class, 'stkQuery'])
        ->name('mpesa.stk-query');
    
    // C2B (Customer to Business)
    Route::post('/c2b/register', [MpesaController::class, 'c2bRegister'])
        ->name('mpesa.c2b.register');
    
    Route::post('/c2b/simulate', [MpesaController::class, 'c2bSimulate'])
        ->name('mpesa.c2b.simulate');
    
    // B2C (Business to Customer)
    Route::post('/b2c', [MpesaController::class, 'b2c'])
        ->name('mpesa.b2c');
    
    // B2B (Business to Business)
    Route::post('/b2b', [MpesaController::class, 'b2b'])
        ->name('mpesa.b2b');
    
    // Account Balance
    Route::post('/balance', [MpesaController::class, 'accountBalance'])
        ->name('mpesa.balance');
    
    // Transaction Status
    Route::post('/transaction-status', [MpesaController::class, 'transactionStatus'])
        ->name('mpesa.transaction-status');
    
    // Reversal
    Route::post('/reversal', [MpesaController::class, 'reversal'])
        ->name('mpesa.reversal');
    
    // Callbacks (M-Pesa will call these endpoints)
    Route::post('/callback/stk', [MpesaController::class, 'stkCallback'])
        ->name('mpesa.callback.stk');
    
    Route::post('/callback/c2b', [MpesaController::class, 'c2bCallback'])
        ->name('mpesa.callback.c2b');
    
    Route::post('/callback/b2c', [MpesaController::class, 'b2cCallback'])
        ->name('mpesa.callback.b2c');
});
