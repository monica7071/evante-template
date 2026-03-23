<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
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
        Paginator::useBootstrapFive();

        Gate::define('super-admin', function ($user) {
            return $user->isSuperAdmin();
        });

        Gate::define('admin-or-above', function ($user) {
            return in_array($user->role, ['super_admin', 'admin']);
        });

        Gate::define('leader-or-above', function ($user) {
            return in_array($user->role, ['super_admin', 'admin', 'leader']);
        });
    }
}
