<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Bus;
use TiMacDonald\ThrottledNotifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\SendThrottledNotificationGroup;
use TiMacDonald\ThrottledNotifications\SendThrottledNotifications;
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
            'payload' => new TestThrottledNotification,
        ]);
        factory(ThrottledNotification::class)->create([
            'notification_id' => $unread->id,
            'payload' => new TestThrottledNotification,
        ]);

        // act
        $this->app->call([new SendThrottledNotifications(now()->addSecond()), 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationGroup::class, 1);
        Bus::assertDispatched(SendThrottledNotificationGroup::class, function (SendThrottledNotificationGroup $job) use ($unread) {
            return $job->databaseNotification()->is($unread);
        });
    }

    public function testSentNotificationsAreIgnored()
    {
        // arrange
        Bus::fake();
        $unsent = factory(ThrottledNotification::class)->create([
            'payload' => new TestThrottledNotification,
        ]);
        factory(ThrottledNotification::class)->states(['sent'])->create([
            'payload' => new TestThrottledNotification,
        ]);

        // act
        $this->app->call([new SendThrottledNotifications(now()->addSecond()), 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationGroup::class, 1);
        Bus::assertDispatched(SendThrottledNotificationGroup::class, function (SendThrottledNotificationGroup $job) use ($unsent) {
            return $job->databaseNotification()->throttledNotification->is($unsent);
        });
    }

    public function testOnlyThrottledNotificationsOlderThanSpecifiedMinutesAreQueued()
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        Bus::fake();
        $old = factory(ThrottledNotification::class)->create([
            'payload' => new TestThrottledNotification,
            'created_at' => Carbon::now()->subMinutes(22)->subSeconds(1),
        ]);
        factory(ThrottledNotification::class)->create([
            'payload' => new TestThrottledNotification,
            'created_at' => Carbon::now()->subMinutes(22),
        ]);

        // act
        $this->app->call([new SendThrottledNotifications(now()->subMinutes(22)), 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationGroup::class, 1);
        Bus::assertDispatched(SendThrottledNotificationGroup::class, function (SendThrottledNotificationGroup $job) use ($old) {
            return $job->databaseNotification()->is($old->databaseNotification);
        });
    }

    public function testOnlyDispatchesOneJobPerNotifiableThatIsTheOldest()
    {
        // arrange
        Bus::fake();
        $databaseNotifications = factory(DatabaseNotification::class)->times(2)->create([
            'notifiable_type' => 'ExpectedType',
            'notifiable_id' => 1722,
        ]);
        $oldest = factory(ThrottledNotification::class)->create([
            'notification_id' => $databaseNotifications[0]->id,
            'payload' => new TestThrottledNotification,
            'created_at' => now()->subMinutes(12),
        ]);
        factory(ThrottledNotification::class)->create([
            'notification_id' => $databaseNotifications[1]->id,
            'payload' => new TestThrottledNotification,
            'created_at' => now()->subMinutes(11),
        ]);

        // act
        $this->app->call([new SendThrottledNotifications(now()->subMinutes(10)), 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationGroup::class, 1);
        Bus::assertDispatched(SendThrottledNotificationGroup::class, function (SendThrottledNotificationGroup $job) use ($oldest) {
            return $job->databaseNotification()->is($oldest->databaseNotification);
        });
    }
}
