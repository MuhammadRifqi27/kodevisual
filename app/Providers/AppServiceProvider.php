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
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Builder::defaultStringLength(191);
        KTBootstrap::init();
    }
}
