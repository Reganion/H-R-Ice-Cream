<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Customer\CustomerPageController;
use App\Http\Controllers\Customer\CustomerAuthController;

use App\Http\Controllers\Admin\AdminPagesController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\FlavorController;
use App\Http\Controllers\Admin\GallonController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\AdminController;


Route::get('/', [CustomerPageController::class, 'landing'])
    ->name('landing');

Route::get('/home', [CustomerPageController::class, 'home'])
    ->name('customer.home');

Route::get('/customer/dashboard', [CustomerPageController::class, 'dashboard'])
    ->name('customer.dashboard');

Route::get('/top-orders', [CustomerPageController::class, 'topOrders'])->name('customer.topOrders');
Route::get('/order/{id}', [CustomerPageController::class, 'orderDetail'])->name('customer.order.detail');
Route::get('/customer/order-history', [CustomerPageController::class, 'orderHistory'])->name('customer.order.history');
Route::get('/customer/favorite', [CustomerPageController::class, 'favorite'])->name('customer.favorite');
Route::get('/customer/messages', [CustomerPageController::class, 'messages'])->name('customer.messages');
Route::get('/customer/chat/{id}', [CustomerPageController::class, 'chat'])->name('customer.chat');
Route::get('/customer/flavors', [CustomerPageController::class, 'flavors'])->name('customer.flavors');
Route::get('/about', [CustomerPageController::class, 'about'])->name('customer.about');
Route::get('/Customer/login', [CustomerPageController::class, 'Customerlogin'])->name('customer.login');
Route::post('/Customer/login', [CustomerAuthController::class, 'login'])->name('customer.login.submit');
Route::get('/Customer/register', [CustomerPageController::class, 'register'])->name('customer.register');
Route::post('/Customer/register', [CustomerAuthController::class, 'register'])->name('customer.register.submit');
Route::get('/Customer/verify-otp', [CustomerAuthController::class, 'showOtpForm'])->name('customer.verify-otp');
Route::post('/Customer/verify-otp', [CustomerAuthController::class, 'verifyOtp'])->name('customer.verify-otp.submit');
Route::post('/Customer/resend-otp', [CustomerAuthController::class, 'resendOtp'])->name('customer.resend-otp');

Route::get('/Customer/forgot-password', [CustomerAuthController::class, 'showForgotPasswordForm'])->name('customer.forgot-password');
Route::post('/Customer/forgot-password', [CustomerAuthController::class, 'sendForgotPasswordOtp'])->name('customer.forgot-password.submit');
Route::get('/Customer/forgot-password/verify-otp', [CustomerAuthController::class, 'showForgotPasswordOtpForm'])->name('customer.forgot-password.verify-otp');
Route::post('/Customer/forgot-password/verify-otp', [CustomerAuthController::class, 'verifyForgotPasswordOtp'])->name('customer.forgot-password.verify-otp.submit');
Route::post('/Customer/forgot-password/resend-otp', [CustomerAuthController::class, 'resendForgotPasswordOtp'])->name('customer.forgot-password.resend-otp');
Route::get('/Customer/forgot-password/reset-password', [CustomerAuthController::class, 'showResetPasswordForm'])->name('customer.forgot-password.reset-password');
Route::post('/Customer/forgot-password/reset-password', [CustomerAuthController::class, 'updatePassword'])->name('customer.forgot-password.reset-password.submit');

Route::get('/customer/auth/google', [CustomerAuthController::class, 'redirectToGoogle'])->name('customer.login.google');
Route::get('/customer/auth/google/callback', [CustomerAuthController::class, 'handleGoogleCallback'])->name('customer.login.google.callback');

Route::get('/driver', function () {
    return view('driver.landing');
})->name('driver.landing');

Route::get('/customer/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

Route::get('/admin/login', [AdminPagesController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/register', [AdminAuthController::class, 'register'])->name('admin.register.submit');
Route::get('/admin/auth/google', [AdminAuthController::class, 'redirectToGoogle'])->name('admin.login.google');
Route::get('/admin/auth/google/callback', [AdminAuthController::class, 'handleGoogleCallback'])->name('admin.login.google.callback');
Route::get('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminPagesController::class, 'dashboard'])->name('dashboard');
    Route::get('/flavors', [AdminPagesController::class, 'flavors'])->name('flavors');
    Route::get('/ingredients', [AdminPagesController::class, 'ingredients'])->name('ingredients');
    Route::get('/gallon', [AdminPagesController::class, 'gallon'])->name('gallon');
    Route::get('/orders', [AdminPagesController::class, 'orders'])->name('orders');
    Route::get('/drivers', [AdminPagesController::class, 'drivers'])->name('drivers');
    Route::get('/customer', [AdminPagesController::class, 'customer'])->name('customer');
    Route::get('/reports', [AdminPagesController::class, 'reports'])->name('reports');
    Route::get('/archive', [AdminPagesController::class, 'archive'])->name('archive');
    Route::get('/account', [AdminPagesController::class, 'account'])->name('account');
    Route::get('/admins/create', [AdminPagesController::class, 'addAdmin'])->name('admins.create');
});


Route::prefix('admin')->group(function () {
    Route::post('/ingredients', [IngredientController::class, 'store'])
        ->name('admin.ingredients.store');

    Route::put('/ingredients/{id}', [IngredientController::class, 'update'])
        ->name('admin.ingredients.update');

    Route::delete('/ingredients/{id}', [IngredientController::class, 'destroy'])
        ->name('admin.ingredients.destroy');
});

Route::prefix('admin')->group(function () {
    Route::post('/drivers', [DriverController::class, 'store'])
        ->name('admin.drivers.store');
});

Route::prefix('admin')->group(function () {
    Route::post('/admins', [AdminController::class, 'store'])
        ->name('admin.admins.store');
});

Route::prefix('admin')->group(function () {

    Route::post('/flavors', [FlavorController::class, 'flavorstore'])
        ->name('admin.flavors.store');

    Route::put('/flavors/{id}', [FlavorController::class, 'flavorupdate'])
        ->name('admin.flavors.update');

    Route::delete('/flavors/{id}', [FlavorController::class, 'flavordestroy'])
        ->name('admin.flavors.destroy');

});

Route::prefix('admin')->group(function () {

    Route::post('/gallons', [GallonController::class, 'gallonstore'])
        ->name('admin.gallons.store');

Route::put('/gallons/{id}', [GallonController::class, 'gallonupdate'])
    ->name('admin.gallons.update');

Route::delete('/gallons/{id}', [GallonController::class, 'gallondestroy'])
    ->name('admin.gallons.destroy');

});


Route::prefix('admin')->name('admin.')->group(function () {
    Route::post('/orders/walkin', [AdminOrderController::class, 'storeWalkIn'])
        ->name('orders.walkin');
    Route::put('/orders/{id}', [AdminOrderController::class, 'updateWalkIn'])
        ->name('orders.update');
});


