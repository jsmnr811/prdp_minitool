<?php

namespace App\Providers;

// use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }


    protected $policies = [
        // Register any model policies here
    ];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Allow superadministrator to bypass all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('superadministrator') ? true : null;
        });
          Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, [
                'work.jasaure@gmail.com',  // <-- Add authorized emails here
            ]);
        });
    }
}
