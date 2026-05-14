<?php

namespace App\Providers;

use App\Models\Organization;
use App\Observers\OrganizationObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
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

        // Custom Blade directive for permission checks
        Blade::if('permission', function (string $permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });

        // Register observers
        Organization::observe(OrganizationObserver::class);
    }
}
