<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TelegramService;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    // ...


public function boot()
{
    if (env('APP_ENV') !== 'local') {
        URL::forceScheme('https');
    }
}

    public function register(): void
    {
        $this->app->singleton(TelegramService::class, function ($app) {
            return new TelegramService();
        });
    }
}
