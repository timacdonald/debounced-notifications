<?php

namespace Tests;

use Illuminate\Support\Facades\Bus;
use TiMacDonald\ThrottledNotifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\SendGroupedNotificationStrategy;
use TiMacDonald\ThrottledNotifications\SendThrottledNotificationGroup;

class SendGroupedNotificationStrategyTest extends TestCase
{
    public function testDispatchesJob()
    {
        // arrange
        Bus::fake();
        $expected = new DatabaseNotification;

        // act
        $this->app[SendGroupedNotificationStrategy::class]->send()($expected);

        // assert
        Bus::assertDispatched(SendThrottledNotificationGroup::class, 1);
        Bus::assertDispatched(SendThrottledNotificationGroup::class, function ($notification) use ($expected) {
            $this->assertSame($expected, $notification->databaseNotification());
            return true;
        });
    }
}
