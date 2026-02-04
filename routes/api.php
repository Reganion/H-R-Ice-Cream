<?php

use App\Http\Controllers\Api\ApiAddressController;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiCartController;
use App\Http\Controllers\Api\ApiFavoriteController;
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

    Route::get('/best-sellers', [ApiFlavorController::class, 'bestSellers']);
    Route::get('/popular', [ApiFlavorController::class, 'popular']);
    Route::get('/flavors', [ApiFlavorController::class, 'index']);
    Route::get('/flavors/{id}', [ApiFlavorController::class, 'show']);
    Route::get('/gallons', [ApiFlavorController::class, 'gallons']);

    // Protected (require session token: Authorization: Bearer {token} or X-Session-Token)
    Route::middleware('api.customer')->group(function () {
        Route::post('/logout', [ApiAuthController::class, 'logout']);
        Route::get('/me', [ApiAuthController::class, 'me']);
        Route::get('/profile', [ApiAuthController::class, 'profile']);
        Route::post('/profile/update', [ApiAuthController::class, 'updateProfile']);
        // Account: fetch logged-in account (account information) and update profile
        Route::get('/account', [ApiAuthController::class, 'account']);
        Route::post('/account/update', [ApiAuthController::class, 'updateProfile']);
        Route::put('/address', [ApiAuthController::class, 'updateAddress']);
        Route::post('/address', [ApiAuthController::class, 'updateAddress']);
        // Customer addresses (clone table linked by customer_id)
        Route::get('/addresses', [ApiAddressController::class, 'index']);
        Route::post('/addresses', [ApiAddressController::class, 'store']);
        Route::get('/addresses/{id}', [ApiAddressController::class, 'show']);
        Route::put('/addresses/{id}', [ApiAddressController::class, 'update']);
        Route::patch('/addresses/{id}', [ApiAddressController::class, 'update']);
        Route::delete('/addresses/{id}', [ApiAddressController::class, 'destroy']);
        Route::post('/addresses/{id}/default', [ApiAddressController::class, 'setDefault']);
        // Change password: email → send OTP → verify OTP → update (current + new password, keep_logged_in)
        Route::post('/change-password/send-otp', [ApiAuthController::class, 'changePasswordSendOtp']);
        Route::post('/change-password/verify-otp', [ApiAuthController::class, 'changePasswordVerifyOtp']);
        Route::post('/change-password/resend-otp', [ApiAuthController::class, 'changePasswordResendOtp']);
        Route::post('/change-password/update', [ApiAuthController::class, 'changePasswordUpdate']);
        Route::get('/orders', [ApiOrderController::class, 'index']);
        Route::post('/orders', [ApiOrderController::class, 'store']);
        Route::get('/orders/{id}', [ApiOrderController::class, 'show']);
        // Favorites (heart icon): list, add/remove toggle, check, delete
        Route::get('/favorites', [ApiFavoriteController::class, 'index']);
        Route::post('/favorites', [ApiFavoriteController::class, 'store']);
        Route::get('/favorites/check', [ApiFavoriteController::class, 'check']);
        Route::delete('/favorites/{flavor_id}', [ApiFavoriteController::class, 'destroy']);
        // Cart: list, add, update quantity, remove
        Route::get('/cart', [ApiCartController::class, 'index']);
        Route::post('/cart', [ApiCartController::class, 'store']);
        Route::put('/cart/{id}', [ApiCartController::class, 'update']);
        Route::patch('/cart/{id}', [ApiCartController::class, 'update']);
        Route::delete('/cart/{id}', [ApiCartController::class, 'destroy']);
    });
});
