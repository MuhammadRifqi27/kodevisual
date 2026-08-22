<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\KTBootstrap;
use Illuminate\Database\Schema\Builder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {
        $this->app->bind(
            \App\Repositories\Auth\AuthRepository::class,
            \App\Repositories\Auth\AuthRepositoryImplement::class
        );
        $this->app->bind(
            \App\Services\Auth\AuthService::class,
            \App\Services\Auth\AuthServiceImplement::class
        );
        $this->app->bind(
            \App\Repositories\Role\RoleRepository::class,
            \App\Repositories\Role\RoleRepositoryImplement::class
        );
        $this->app->bind(
            \App\Services\Role\RoleService::class,
            \App\Services\Role\RoleServiceImplement::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Builder::defaultStringLength(191);
        KTBootstrap::init();
    }
}
