<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Notifiable;
use Tests\DummyThrottledNotification;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\ThrottledNotification;

class ThrottleChannelTest extends TestCase
{
    public function testDatabaseNotificationIsCreated(): void
    {
        // arrange
        $notifiable = \factory(Notifiable::class)->create();
        $notification = new DummyThrottledNotification();

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, DatabaseNotification::count());
    }

    public function testThrottledNotificationIsCreated(): void
    {
        // arrange
        $notifiable = \factory(Notifiable::class)->create();
        $notification = new DummyThrottledNotification();

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, ThrottledNotification::count());
    }
}
