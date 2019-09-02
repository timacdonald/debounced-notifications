<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * @var string
     */
    private $configPath = __DIR__.'/../config/throttled-notifications.php';

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../migrations');

            $this->publishes([
                $this->configPath => \config_path('throttled-notifications.php'),
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath, 'throttled-notifications');
    }
}
