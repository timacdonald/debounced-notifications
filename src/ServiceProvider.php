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

        $this->app->bind(Contracts\NotifiablesQuery::class, NotifiablesQuery::class);
    }
}
