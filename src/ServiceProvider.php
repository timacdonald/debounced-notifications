<?php

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
    }
}
