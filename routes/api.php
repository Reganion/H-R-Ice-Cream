<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiFlavorController;
use App\Http\Controllers\Api\ApiOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (for Flutter / mobile app)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Public
    Route::post('/login', [ApiAuthController::class, 'login']);
    Route::post('/register', [ApiAuthController::class, 'register']);
    Route::post('/verify-otp', [ApiAuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [ApiAuthController::class, 'resendOtp']);

    // Forgot password (email -> OTP -> verify -> reset)
    Route::post('/forgot-password', [ApiAuthController::class, 'forgotPassword']);
    Route::post('/forgot-password/resend-otp', [ApiAuthController::class, 'resendForgotPasswordOtp']);
    Route::post('/forgot-password/verify-otp', [ApiAuthController::class, 'verifyForgotPasswordOtp']);
    Route::post('/forgot-password/reset-password', [ApiAuthController::class, 'resetPassword']);

    Route::get('/flavors', [ApiFlavorController::class, 'index']);
    Route::get('/flavors/{id}', [ApiFlavorController::class, 'show']);
    Route::get('/gallons', [ApiFlavorController::class, 'gallons']);

    // Protected (require Bearer token after installing Laravel Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [ApiAuthController::class, 'logout']);
        Route::get('/me', [ApiAuthController::class, 'me']);
        Route::get('/orders', [ApiOrderController::class, 'index']);
        Route::post('/orders', [ApiOrderController::class, 'store']);
        Route::get('/orders/{id}', [ApiOrderController::class, 'show']);
    });
});
