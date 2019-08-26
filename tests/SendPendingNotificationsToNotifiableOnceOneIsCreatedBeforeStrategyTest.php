<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Bus;
use TiMacDonald\ThrottledNotifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\SendPendingNotificationsToNotifiableOnceOneIsCreatedBeforeStrategy;
use TiMacDonald\ThrottledNotifications\SendThrottledNotificationGroup;
use TiMacDonald\ThrottledNotifications\SendThrottledNotifications;
use TiMacDonald\ThrottledNotifications\ThrottledNotification;

class SendPendingNotificationsToNotifiableOnceOneIsCreatedBeforeStrategyTest extends TestCase
{
    public function testReadNotificationsAreIgnored()
    {
        // arrange
        $result = collect();
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
        $this->app->make(SendPendingNotificationsToNotifiableOnceOneIsCreatedBeforeStrategy::class, ['date' => now()->addSecond()])
            ->handle(function ($notification) use ($result) {
                $result[] = $notification;
            });

        // assert
        $this->assertCount(1, $result);
        $this->assertTrue($result[0]->is($unread));
    }

    public function testSentNotificationsAreIgnored()
    {
        // arrange
        $result = collect();
        $unsent = factory(ThrottledNotification::class)->create([
            'payload' => new TestThrottledNotification,
        ]);
        factory(ThrottledNotification::class)->states(['sent'])->create([
            'payload' => new TestThrottledNotification,
        ]);

        // act
        $this->app->make(SendPendingNotificationsToNotifiableOnceOneIsCreatedBeforeStrategy::class, ['date' => now()->addSecond()])
            ->handle(function ($notification) use ($result) {
                $result[] = $notification;
            });

        // assert
        $this->assertCount(1, $result);
        $this->assertTrue($result[0]->throttledNotification->is($unsent));
    }

    public function testOnlyThrottledNotificationsOlderThanSpecifiedMinutesAreQueued()
    {
        // arrange
        Carbon::setTestNow(now());
        $result = collect();
        $old = factory(ThrottledNotification::class)->create([
            'payload' => new TestThrottledNotification,
            'created_at' => now()->subMinutes(22)->subSeconds(1),
        ]);
        factory(ThrottledNotification::class)->create([
            'payload' => new TestThrottledNotification,
            'created_at' => now()->subMinutes(22),
        ]);

        // act
        $this->app->make(SendPendingNotificationsToNotifiableOnceOneIsCreatedBeforeStrategy::class, ['date' => now()->subMinutes(22)])
            ->handle(function ($notification) use ($result) {
                $result[] = $notification;
            });

        // assert
        $this->assertCount(1, $result);
        $this->assertTrue($result[0]->throttledNotification->is($old));
    }

    public function testOnlyDispatchesOneJobPerNotifiableThatIsTheOldest()
    {
        // arrange
        $result = collect();
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
        $this->app->make(SendPendingNotificationsToNotifiableOnceOneIsCreatedBeforeStrategy::class, ['date' => now()->subMinutes(10)])
            ->handle(function ($notification) use ($result) {
                $result[] = $notification;
            });

        // assert
        $this->assertCount(1, $result);
        $this->assertTrue($result[0]->throttledNotification->is($oldest));
    }
}
