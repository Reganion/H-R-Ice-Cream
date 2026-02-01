<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['admin.layout.layout', 'admin.account'], function ($view) {
            $adminUser = null;
            if (session()->has('admin_id')) {
                $adminUser = User::find(session('admin_id'));
            }
            $view->with('adminUser', $adminUser);
        });
    }
}
