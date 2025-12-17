<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\AdminPolicy;

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
        // Register the AdminPolicy
        //Gate::policy(User::class, AdminPolicy::class);
        // Register the UserPolicy
        Gate::policy(User::class, \App\Policies\UserPolicy::class);

        // Define Gates for AdminPolicy actions
        Gate::define('viewAdminPanel', [AdminPolicy::class, 'viewAdminPanel']);
        Gate::define('manageUsers', [AdminPolicy::class, 'manageUsers']);
        Gate::define('changeAdminStatus', [AdminPolicy::class, 'changeAdminStatus']);
        Gate::define('toggleUserStatus', [AdminPolicy::class, 'toggleUserStatus']);
        Gate::define('deleteUser', [AdminPolicy::class, 'deleteUser']);
    }
}