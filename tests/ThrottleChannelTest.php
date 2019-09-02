<?php

declare(strict_types=1);

namespace Tests;

use Carbon\Carbon;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\ThrottledNotification;

class ThrottleChannelTest extends TestCase
{
    public function testDatabaseNotificationIsCreated(): void
    {
        // arrange
        $notifiable = factory(Notifiable::class)->create();
        $notification = new DummyThrottledNotification();

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, DatabaseNotification::count());
    }

    public function testThrottledNotificationIsCreated(): void
    {
        // arrange
        $notifiable = factory(Notifiable::class)->create();
        $notification = new DummyThrottledNotification();

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, ThrottledNotification::count());
    }

    public function testNotDelayedIfNotifiableDoesntImplementDelayUntilMethod(): void
    {
        // arrange
        $notifiable = factory(Notifiable::class)->create();
        $notification = new DummyThrottledNotification();

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, ThrottledNotification::whereNotDelayed()->count());
    }

    public function testDelayedIfNotifiableImplementsDelayUntilMethodWithFutureDate(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::parse('2019-09-10 12:51:00'));
        $notifiable = new class() extends Notifiable {
            protected $attributes = ['id' => 54321];

            public function delayNotificationsUntil()
            {
                return now()->addDay();
            }
        };
        $notification = new DummyThrottledNotification();

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertCount(1, $notifications = ThrottledNotification::whereDelayed()->get());
        $this->assertSame('2019-09-11 12:51:00', $notifications[0]->delayed_until->format('Y-m-d H:i:s'));
    }

    public function testNotDelayedIfNotifiableImplementsDelayUntilMethodWithNow(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::parse('2019-09-10 12:51:00'));
        $notifiable = new class() extends Notifiable {
            protected $attributes = ['id' => 54321];

            public function delayNotificationsUntil()
            {
                return now();
            }
        };
        $notification = new DummyThrottledNotification();

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, ThrottledNotification::whereNotDelayed()->count());
    }

    public function testNotDelayedIfNotifiableImplementsDelayUntilMethodWithPastDate(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::parse('2019-09-10 12:51:00'));
        $notifiable = new class() extends Notifiable {
            protected $attributes = ['id' => 54321];

            public function delayNotificationsUntil()
            {
                return now()->subMinute();
            }
        };
        $notification = new DummyThrottledNotification();

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, ThrottledNotification::whereNotDelayed()->count());
    }
}
