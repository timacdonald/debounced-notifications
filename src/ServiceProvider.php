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
        $this->app->bind(\TiMacDonald\ThrottledNotifications\Contracts\Wait::class, static function () {
            return \TiMacDonald\ThrottledNotifications\Wait::fromMinutes(10);
        });
    }
}
