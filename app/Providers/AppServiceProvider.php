<?php

namespace App\Providers;

use App\Services\GensparkService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GensparkService::class, function () {
            return new GensparkService();
        });
    }

    public function boot(): void
    {
        //
    }
}
