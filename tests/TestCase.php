<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Orchestra\Testbench\TestCase as BaseTestCase;
use TiMacDonald\ThrottledNotifications\ServiceProvider;
use TiMacDonald\ThrottledNotifications\ShouldThrottle;
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

class TestThrottledNotification extends Notification implements ShouldThrottle
{
    use Throttleable;

    public $constructorArgs;

    public function __construct()
    {
        $this->constructorArgs = func_get_args();
    }

    public function throttledVia($notifiable): array
    {
        return [];
    }
}

class TestNotifiable extends Model
{
    use Notifiable;

    protected $guarded = [];

    protected $attributes = [
        'id' => 4321,
    ];
}
