<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Bus;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;
use TiMacDonald\ThrottledNotifications\Jobs\SendThrottledNotifications;
use TiMacDonald\ThrottledNotifications\Jobs\SendThrottledNotificationsToNotifiable;

class SendThrottledNotificationsTest extends TestCase
{
    public function testDispatchesJobs(): void
    {
        // arrange
        Bus::fake();
        $throttledNotification = \factory(ThrottledNotification::class)->create();
        \assert($throttledNotification instanceof ThrottledNotification);
        Carbon::setTestNow(Carbon::now()->addMinutes(10));

        // act
        $this->app->call([new SendThrottledNotifications(), 'handle']);

        // assert
        /** @var \TiMacDonald\ThrottledNotifications\Jobs\SendThrottledNotificationsToNotifiable[] */
        $jobs = Bus::dispatched(SendThrottledNotificationsToNotifiable::class);
        $this->assertCount(1, $jobs);
        $this->assertTrue($throttledNotification->databaseNotification->notifiable->is($jobs[0]->notifiable()));
    }

    public function testDifferentUuidProvidedToEachDispatchedJob(): void
    {
        // arrange
        Bus::fake();
        $first = \factory(ThrottledNotification::class)->create();
        \assert($first instanceof ThrottledNotification);
        $second = \factory(ThrottledNotification::class)->create();
        \assert($second instanceof ThrottledNotification);
        Carbon::setTestNow(Carbon::now()->addMinutes(10));

        // act
        $this->app->call([new SendThrottledNotifications(), 'handle']);

        // assert
        /** @var \TiMacDonald\ThrottledNotifications\Jobs\SendThrottledNotificationsToNotifiable[] */
        $jobs = Bus::dispatched(SendThrottledNotificationsToNotifiable::class);
        $this->assertCount(2, $jobs);
        $this->assertTrue(Str::isUuid($jobs[0]->key()));
        $this->assertTrue(Str::isUuid($jobs[1]->key()));
        $this->assertNotSame($jobs[0]->key(), $jobs[1]->key());
    }
}
