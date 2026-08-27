<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;


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
        if (
            app()->environment('production') ||
            request()->isSecure() ||
            request()->header('x-forwarded-proto') === 'https' ||
            str_contains(request()->header('host') ?? '', 'ngrok') ||
            str_contains(request()->url(), 'ngrok') ||
            str_contains(config('app.url'), 'https://')
        ) {
            URL::forceScheme('https');
            request()->server->set('HTTPS', 'on');
        }
    }
}
