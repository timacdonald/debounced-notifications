<?php

namespace Tests;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\ThrottleChannel;
use TiMacDonald\ThrottledNotifications\ThrottledNotification;

class ThrottleChannelTest extends TestCase
{
    public function test_database_notification_is_created()
    {
        // arrange
        $notifiable = new TestNotifiable;
        $notification = new TestThrottledNotification;

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, DatabaseNotification::count());
    }

    public function test_throttled_notification_is_created()
    {
        // arrange
        $notifiable = new TestNotifiable;
        $notification = new TestThrottledNotification;

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, ThrottledNotification::count());
    }
}

