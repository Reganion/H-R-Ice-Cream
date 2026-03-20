<?php

namespace App\Providers;

use App\Models\AdminNotification;
use App\Models\CustomerNotification;
use App\Models\DriverNotification;
use App\Models\User;
use App\Observers\CustomerNotificationObserver;
use App\Observers\DriverNotificationObserver;
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
        CustomerNotification::observe(CustomerNotificationObserver::class);
        DriverNotification::observe(DriverNotificationObserver::class);

        View::composer(['admin.layout.layout', 'admin.account', 'admin.support-centre'], function ($view) {
            $adminUser = null;
            $adminNotifications = collect();
            if (session()->has('admin_id')) {
                $adminUser = User::find(session('admin_id'));
                if ($adminUser) {
                    $adminNotifications = AdminNotification::forUser($adminUser->id)
                        ->whereIn('type', AdminNotification::orderLifecycleTypes())
                        ->orderBy('created_at', 'desc')
                        ->limit(50)
                        ->get();
                }
            }
            $view->with('adminUser', $adminUser)->with('adminNotifications', $adminNotifications);
        });
    }
}
