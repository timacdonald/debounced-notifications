<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Bus;
use TiMacDonald\ThrottledNotifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\SendThrottledNotifications;
use TiMacDonald\ThrottledNotifications\SendThrottledNotificationGroup;
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
        ]);
        factory(ThrottledNotification::class)->create([
            'notification_id' => $unread->id,
        ]);

        // act
        $this->app->call([new SendThrottledNotifications, 'handle']);

        // assert
        //$this->assertCount(1, $result);
        //$this->assertTrue($result[0]->is($unread));
    }

    public function testSentNotificationsAreIgnored()
    {
        // arrange
        $result = collect();
        $unsent = factory(ThrottledNotification::class)->create();
        factory(ThrottledNotification::class)->states(['sent'])->create();

        // act
        $this->app->make(SendThrottledNotifications::class, ['date' => now()->addSecond()])
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
            'created_at' => now()->subMinutes(22)->subSeconds(1),
        ]);
        factory(ThrottledNotification::class)->create([
            'created_at' => now()->subMinutes(22),
        ]);

        // act
        $this->app->make(SendThrottledNotifications::class, ['date' => now()->subMinutes(22)])
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
        $databaseNotification = factory(DatabaseNotification::class)->create();
        $oldest = factory(ThrottledNotification::class)->create([
            'notification_id' => $databaseNotification->id,
            'created_at' => now()->subMinutes(12),
        ]);
        factory(ThrottledNotification::class)->create([
            'notification_id' => $databaseNotification->id,
            'created_at' => now()->subMinutes(11),
        ]);

        // act
        $this->app->call([new SendThrottledNotifications, 'handle']);

        // assert
        $this->assertCount(1, $result);
        $this->assertTrue($result[0]->throttledNotification->is($oldest));
    }
}
