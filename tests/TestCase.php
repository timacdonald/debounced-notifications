<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\Notifiable as NotifiableTrait;
use Illuminate\Notifications\Notification;
use Orchestra\Testbench\TestCase as BaseTestCase;
use TiMacDonald\ThrottledNotifications\ServiceProvider;
use TiMacDonald\ThrottledNotifications\Throttleable;

class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        $this->withFactories(__DIR__.'/factories');

        $this->artisan('migrate')->run();
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}

class DummyThrottledNotification extends Notification
{
    use Throttleable;

    public function throttledVia($notifiable): array
    {
        return [];
    }
}

class Notifiable extends Model
{
    use NotifiableTrait;
}

