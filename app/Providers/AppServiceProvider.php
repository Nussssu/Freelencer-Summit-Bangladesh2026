<?php

namespace App\Providers;

use App\Content\HomeContent;
use App\Support\Settings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(HomeContent::class, fn () => HomeContent::fromConfig());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Every public form: 5 posts a minute and 30 an hour per IP (`throttle:forms`).
        RateLimiter::for('forms', fn (Request $request) => [Limit::perMinute(5)->by($request->ip()), Limit::perHour(30)->by($request->ip())]);

        // SMTP saved in the CMS (Settings > Email) overrides .env, applied only when mail is actually used.
        $this->app->afterResolving('mail.manager', fn () => Settings::applyMail());
        Queue::before(fn () => Settings::flush()); // workers are long-lived; read settings fresh per job
    }
}
