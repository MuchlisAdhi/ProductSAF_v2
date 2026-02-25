<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
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
        // Prevent production from accidentally using Vite dev server when public/hot is uploaded.
        if ($this->app->environment('production')) {
            Vite::useHotFile(storage_path('framework/vite.hot'));
        }
    }
}
