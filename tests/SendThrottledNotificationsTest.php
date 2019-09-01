<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Bus;
use TiMacDonald\ThrottledNotifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\SendThrottledNotifications;
use TiMacDonald\ThrottledNotifications\SendThrottledNotificationGroup;
use TiMacDonald\ThrottledNotifications\SendThrottledNotificationsToNotifiable;
use TiMacDonald\ThrottledNotifications\ThrottledNotification;

class SendThrottledNotificationsTest extends TestCase
{
    public function testReadNotificationsAreIgnored()
    {
        // arrange
        Bus::fake();
        $unread = factory(DatabaseNotification::class)->create();
        $read = factory(DatabaseNotification::class)->states(['read'])->create();
        factory(ThrottledNotification::class)->create([
            'notification_id' => $read->id,
            'created_at' => now()->subMinutes(11),
        ]);
        factory(ThrottledNotification::class)->create([
            'notification_id' => $unread->id,
            'created_at' => now()->subMinutes(11),
        ]);

        // act
        $this->app->call([new SendThrottledNotifications, 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, 1);
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, function ($job) use ($unread) {
            $this->assertTrue($job->notifiable()->is($unread->notifiable));
            return true;
        });
    }

    public function testSentNotificationsAreIgnored()
    {
        // arrange
        Bus::fake();
        $unsent = factory(ThrottledNotification::class)->create([
            'created_at' => now()->subMinutes(11),
        ]);
        factory(ThrottledNotification::class)->states(['sent'])->create([
            'created_at' => now()->subMinutes(11),
        ]);

        // act
        $this->app->call([new SendThrottledNotifications, 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, 1);
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, function ($job) use ($unsent) {
            $this->assertTrue($job->notifiable()->is($unsent->databaseNotification->notifiable));
            return true;
        });
    }

    public function testOnlyThrottledNotificationsOlderThanSpecifiedMinutesAreQueued()
    {
        // arrange
        Carbon::setTestNow(now());
        Bus::fake();
        $oldest = factory(ThrottledNotification::class)->create([
            'created_at' => now()->subMinutes(10)->subSeconds(1),
        ]);
        factory(ThrottledNotification::class)->create([
            'created_at' => now()->subMinutes(10),
        ]);

        // act
        $this->app->call([new SendThrottledNotifications, 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, 1);
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, function ($job) use ($oldest) {
            $this->assertTrue($job->notifiable()->is($oldest->databaseNotification->notifiable));
            return true;
        });
    }

    public function testOnlyDispatchesOneJobPerNotifiableThatIsTheOldest()
    {
        // arrange
        Bus::fake();
        $databaseNotification = factory(DatabaseNotification::class)->create();
        factory(ThrottledNotification::class)->create([
            'notification_id' => $databaseNotification->id,
            'created_at' => now()->subMinutes(11),
        ]);
        $oldest = factory(ThrottledNotification::class)->create([
            'notification_id' => $databaseNotification->id,
            'created_at' => now()->subMinutes(12),
        ]);

        // act
        $this->app->call([new SendThrottledNotifications, 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, 1);
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, function ($job) use ($oldest) {
            $this->assertTrue($job->notifiable()->is($oldest->databaseNotification->notifiable));
            return true;
        });
    }
}
