<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthorizationServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define role-based gates
        Gate::define('admin-access', function (User $user) {
            return $user->role === 'Admin';
        });

        Gate::define('school-access', function (User $user) {
            return in_array($user->role, ['School', 'Admin']);
        });

        Gate::define('sponsor-access', function (User $user) {
            return in_array($user->role, ['Sponsor', 'Admin']);
        });

        Gate::define('donor-access', function (User $user) {
            return in_array($user->role, ['Donor', 'Admin']);
        });

        Gate::define('student-access', function (User $user) {
            return in_array($user->role, ['Student/Parent', 'Admin']);
        });

        Gate::define('manage-users', function (User $user) {
            return $user->role === 'Admin';
        });

        Gate::define('manage-opportunities', function (User $user) {
            return in_array($user->role, ['School', 'Admin']);
        });

        Gate::define('apply-opportunities', function (User $user) {
            return in_array($user->role, ['Student/Parent', 'Admin']);
        });

        Gate::define('sponsor-students', function (User $user) {
            return in_array($user->role, ['Sponsor', 'Admin']);
        });

        Gate::define('donate-materials', function (User $user) {
            return in_array($user->role, ['Donor', 'Sponsor', 'Admin']);
        });

        Gate::define('view-analytics', function (User $user) {
            return in_array($user->role, ['School', 'Sponsor', 'Admin']);
        });

        // Resource ownership gates
        Gate::define('own-resource', function (User $user, $resource) {
            return $user->id === $resource->user_id || $user->role === 'Admin';
        });

        Gate::define('manage-school-resources', function (User $user, $resource) {
            return ($user->role === 'School' && $user->id === $resource->user_id) || $user->role === 'Admin';
        });
    }
}
