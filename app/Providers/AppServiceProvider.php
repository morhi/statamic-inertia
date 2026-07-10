<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        // Enforce https for production environment
        if (Str::startsWith(config('app.url'), 'https://')) {
            $this->app['request']->server->set('HTTPS', true);
        }
    }
}
