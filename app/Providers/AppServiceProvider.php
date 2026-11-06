<?php

namespace App\Providers;

use App\Content\HomeContent;
use App\Models\AuditLog;
use App\Models\Lead;
use App\Support\NotifyTeamOfSubmission;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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
        // Event Management › Grand Summit overrides config('summit') (date, venue, tagline, counters).
        Settings::applySummit();
        Queue::before(function () { // workers are long-lived; read settings fresh per job
            Settings::flush();
            Settings::applySummit();
        });

        // Messages › Email & SMS log: App\Listeners\LogSentMessage (auto-discovered, MessageSent).
        // Admin notifications (bell) for each new public submission.
        Event::listen('eloquent.created: '.Lead::class, fn (Lead $lead) => rescue(fn () => NotifyTeamOfSubmission::send($lead), report: false));

        Gate::before(fn (User $user) => $user->isSuperAdmin() ?: null);

        // Audit log: staff CRUD and sign-ins. last_login_at is a quiet write (no updated_at, no audit row of its own).
        foreach (['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted'] as $event => $action) {
            Event::listen("eloquent.{$event}: *", fn (string $name, array $payload) => AuditLog::forModel($action, $payload[0]));
        }
        Event::listen(function (Login $e) {
            if ($e->user instanceof User) {
                User::withoutTimestamps(fn () => $e->user->forceFill(['last_login_at' => now()])->saveQuietly());
                AuditLog::record('Logged in', 'Auth', "{$e->user->email} signed in", $e->user);
            }
        });
        Event::listen(fn (Logout $e) => $e->user && AuditLog::record('Logged out', 'Auth', "{$e->user->email} signed out", $e->user));
    }
}
