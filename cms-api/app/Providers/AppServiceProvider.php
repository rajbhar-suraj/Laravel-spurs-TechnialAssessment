<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Console\Commands\MakeServiceCommand;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Proper API rate limiting configuration
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        if ($this->app->runningInConsole()) {
            $this->commands([MakeServiceCommand::class]);
        }
    }
}