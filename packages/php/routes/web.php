<?php

use Illuminate\Support\Facades\Route;
use Yourdudeken\Mpesa\Facades\Mpesa;

Route::prefix('mpesa')->group(function () {
    Route::post('/stk/callback', function () {
        return Mpesa::validateCallback();
    });

    Route::post('/c2b/confirm', function () {
        return Mpesa::confirmTransaction();
    });

    Route::post('/c2b/validate', function () {
        return Mpesa::validateTransaction();
    });

    Route::post('/b2c/result', function () {
        return Mpesa::b2cResult();
    });

    Route::post('/b2c/timeout', function () {
        return Mpesa::b2cTimeout();
    });

    Route::post('/b2pochi/result', function () {
        return Mpesa::b2pochiResult();
    });

    Route::post('/b2pochi/timeout', function () {
        return Mpesa::b2pochiTimeout();
    });

    Route::post('/balance/result', function () {
        return Mpesa::balanceResult();
    });

    Route::post('/balance/timeout', function () {
        return Mpesa::balanceTimeout();
    });

    Route::post('/reversal/result', function () {
        return Mpesa::reversalResult();
    });

    Route::post('/reversal/timeout', function () {
        return Mpesa::reversalTimeout();
    });
});
