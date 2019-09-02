<?php

declare(strict_types=1);

namespace Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use TiMacDonald\ThrottledNotifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\ThrottledNotification;
use TiMacDonald\ThrottledNotifications\SendThrottledNotifications;
use TiMacDonald\ThrottledNotifications\SendThrottledNotificationsToNotifiable;

class SendThrottledNotificationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::now());
    }

    public function testDispatchesJobsForMultipleNotifiables(): void
    {
        // arrange
        Bus::fake();
        \factory(ThrottledNotification::class)->create([
            'created_at' => Carbon::now()->subMinutes(11),
        ]);
        \factory(ThrottledNotification::class)->create([
            'created_at' => Carbon::now()->subMinutes(11),
        ]);

        // act
        $this->app->call([new SendThrottledNotifications(), 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, 2);
    }

    public function testReadNotificationsAreIgnored(): void
    {
        // arrange
        Bus::fake();
        $unread = \factory(DatabaseNotification::class)->create();
        $read = \factory(DatabaseNotification::class)->states(['read'])->create();
        \factory(ThrottledNotification::class)->create([
            'notification_id' => $read->id,
            'created_at' => Carbon::now()->subMinutes(11),
        ]);
        \factory(ThrottledNotification::class)->create([
            'notification_id' => $unread->id,
            'created_at' => Carbon::now()->subMinutes(11),
        ]);

        // act
        $this->app->call([new SendThrottledNotifications(), 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, 1);
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, function ($job) use ($unread) {
            $this->assertTrue($job->notifiable()->is($unread->notifiable));

            return true;
        });
    }

    public function testDelayedNotificationsAreIgnored(): void
    {
        // arrange
        Bus::fake();
        $expected = \factory(ThrottledNotification::class)->create([
            'created_at' => Carbon::now()->subMinutes(11),
        ]);
        \factory(ThrottledNotification::class)->states(['delayed'])->create([
            'created_at' => Carbon::now()->subMinutes(11),
        ]);

        // act
        $this->app->call([new SendThrottledNotifications(), 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, 1);
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, function ($job) use ($expected) {
            $this->assertTrue($job->notifiable()->is($expected->databaseNotification->notifiable));

            return true;
        });
    }

    public function testSentNotificationsAreIgnored(): void
    {
        // arrange
        Bus::fake();
        $expected = \factory(ThrottledNotification::class)->create([
            'created_at' => Carbon::now()->subMinutes(11),
        ]);
        \factory(ThrottledNotification::class)->states(['sent'])->create([
            'created_at' => Carbon::now()->subMinutes(11),
        ]);

        // act
        $this->app->call([new SendThrottledNotifications(), 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, 1);
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, function ($job) use ($expected) {
            $this->assertTrue($job->notifiable()->is($expected->databaseNotification->notifiable));

            return true;
        });
    }

    public function testOnlyThrottledNotificationsOlderThanSpecifiedMinutesAreQueued(): void
    {
        // arrange
        Bus::fake();
        $expected = \factory(ThrottledNotification::class)->create([
            'created_at' => Carbon::now()->subMinutes(10)->subSeconds(1),
        ]);
        \factory(ThrottledNotification::class)->create([
            'created_at' => Carbon::now()->subMinutes(10),
        ]);

        // act
        $this->app->call([new SendThrottledNotifications(), 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, 1);
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, function ($job) use ($expected) {
            $this->assertTrue($job->notifiable()->is($expected->databaseNotification->notifiable));

            return true;
        });
    }

    public function testOnlyDispatchesOneJobPerNotifiableThatIsTheOldest(): void
    {
        // arrange
        Bus::fake();
        $databaseNotification = \factory(DatabaseNotification::class)->create();
        \factory(ThrottledNotification::class)->create([
            'notification_id' => $databaseNotification->id,
            'created_at' => Carbon::now()->subMinutes(11),
        ]);
        $expected = \factory(ThrottledNotification::class)->create([
            'notification_id' => $databaseNotification->id,
            'created_at' => Carbon::now()->subMinutes(13),
        ]);

        // act
        $this->app->call([new SendThrottledNotifications(), 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, 1);
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, function ($job) use ($expected) {
            $this->assertTrue($job->notifiable()->is($expected->databaseNotification->notifiable));

            return true;
        });
    }

    public function testWaitTimeCanBeConfigured(): void
    {
        // arrange
        Bus::fake();
        Config::set('throttled-notifications.wait', 60);
        $databaseNotification = \factory(DatabaseNotification::class)->create();
        \factory(ThrottledNotification::class)->create([
            'created_at' => Carbon::now()->subSeconds(60),
        ]);
        $expected = \factory(ThrottledNotification::class)->create([
            'created_at' => Carbon::now()->subSeconds(61),
        ]);

        // act
        $this->app->call([new SendThrottledNotifications(), 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, 1);
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, function ($job) use ($expected) {
            $this->assertTrue($job->notifiable()->is($expected->databaseNotification->notifiable));

            return true;
        });
    }

    public function testReservedNotificationsAreIgnored(): void
    {
        // arrange
        Bus::fake();
        $expected = \factory(ThrottledNotification::class)->create([
            'created_at' => Carbon::now()->subMinutes(11),
        ]);
        \factory(ThrottledNotification::class)->states(['delayed'])->create([
            'created_at' => Carbon::now()->subMinutes(11),
        ]);

        // act
        $this->app->call([new SendThrottledNotifications(), 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, 1);
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, function ($job) use ($expected) {
            $this->assertTrue($job->notifiable()->is($expected->databaseNotification->notifiable));

            return true;
        });
    }
}
