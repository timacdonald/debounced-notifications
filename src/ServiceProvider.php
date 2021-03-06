<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../migrations');
        }
    }

    public function register(): void
    {
        $this->app->bind(Contracts\Wait::class, static function () {
            return Wait::fromMinutes(10);
        });

        $this->app->bind(Contracts\Delay::class, Delay::class);

        $this->app->bind(Contracts\Courier::class, Courier::class);

        $this->app->bind(Contracts\Notifiables::class, Queries\Notifiables::class);

        $this->app->bind(Contracts\Reservables::class, Queries\Reservables::class);
    }
}
