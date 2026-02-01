<?php

namespace App\Providers;

use App\Services\FirebaseRealtimeService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Contract\Database as FirebaseDatabase;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FirebaseRealtimeService::class, function ($app) {
            return new FirebaseRealtimeService($app->make(FirebaseDatabase::class));
        });
        $this->app->alias(FirebaseRealtimeService::class, 'firebase.realtime');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['admin.layout.layout', 'admin.account'], function ($view) {
            $adminUser = null;
            if (session()->has('admin_id')) {
                $db = app(FirebaseRealtimeService::class);
                $adminUser = $db->get('users', session('admin_id'));
            }
            $view->with('adminUser', $adminUser);
        });
    }
}
