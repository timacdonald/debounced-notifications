<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Orchestra\Testbench\TestCase as BaseTestCase;
use TiMacDonald\ThrottledNotifications\Throttleable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use TiMacDonald\ThrottledNotifications\ServiceProvider;
use Illuminate\Notifications\Notifiable as NotifiableTrait;

class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/migrations');

        $this->withFactories(__DIR__.'/factories');

        $this->artisan('migrate')->run();
    }

    protected function getPackageProviders($app): array
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
