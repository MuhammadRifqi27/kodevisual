<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
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
        try {
            // Register old permissions (backward compatibility)
            $oldPermissions = \App\Models\Permission::all();
            foreach ($oldPermissions as $permission) {
                \Illuminate\Support\Facades\Gate::define($permission->name, function ($user) use ($permission) {
                    return $user->hasPermission($permission->name);
                });
            }

            // Register new app-specific permissions
            // Format: app_code.permission_code (e.g., money-management.dashboard)
            $appPermissions = \App\Models\AppPermission::with('app')->get();
            foreach ($appPermissions as $permission) {
                $gateName = $permission->app->code . '.' . $permission->code;
                \Illuminate\Support\Facades\Gate::define($gateName, function ($user) use ($gateName) {
                    return $user->hasPermission($gateName);
                });
            }

            // Register app-level gates (e.g., "money-management")
            $apps = \App\Models\App::all();
            foreach ($apps as $app) {
                \Illuminate\Support\Facades\Gate::define($app->code, function ($user) use ($app) {
                    return $user->apps->contains('id', $app->id);
                });
            }
        } catch (\Exception $e) {
            // Table might not exist yet
        }
    }
}
