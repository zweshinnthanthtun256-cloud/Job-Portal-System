<?php

namespace App\Providers;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
        Event::listen(Login::class, function (Login $event) {
            ActivityLog::create(['user_id' => $event->user->id, 'event' => 'user.login', 'subject_type' => $event->user->getMorphClass(), 'subject_id' => $event->user->getKey(), 'ip_address' => request()->ip(), 'user_agent' => substr((string) request()->userAgent(), 0, 1000)]);
        });
        Event::listen(Logout::class, fn (Logout $event) => $this->recordAuthEvent('user.logout', $event->user));
        Event::listen(Registered::class, fn (Registered $event) => $this->recordAuthEvent('user.registered', $event->user));
        Event::listen(Verified::class, fn (Verified $event) => $this->recordAuthEvent('user.email_verified', $event->user));
        Event::listen(PasswordReset::class, fn (PasswordReset $event) => $this->recordAuthEvent('user.password_reset', $event->user));
    }

    private function recordAuthEvent(string $event, $user): void
    {
        if (! $user) {
            return;
        }
        ActivityLog::create(['user_id' => $user->id, 'event' => $event, 'subject_type' => $user->getMorphClass(), 'subject_id' => $user->getKey(), 'ip_address' => request()->ip(), 'user_agent' => substr((string) request()->userAgent(), 0, 1000)]);
    }
}
