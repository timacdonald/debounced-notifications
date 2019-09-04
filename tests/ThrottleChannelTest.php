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

    public function testNotDelayedIfNotifiableDoesntImplementDelayUntilMethod(): void
    {
        // arrange
        $notifiable = \factory(Notifiable::class)->create();
        $notification = new DummyThrottledNotification();

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, ThrottledNotification::whereNull('delayed_until')->count());
    }

    public function testDelayedIfNotifiableImplementsDelayUntilMethodWithFutureDate(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::parse('2019-09-10 12:51:00'));
        $notifiable = new class() extends Notifiable {
            protected $attributes = ['id' => 54321];

            public function delayNotificationsUntil()
            {
                return Carbon::now()->addDay();
            }
        };
        $notification = new DummyThrottledNotification();

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertCount(1, $notifications = ThrottledNotification::whereNotNull('delayed_until')->get());
        $this->assertSame('2019-09-11 12:51:00', $notifications[0]->delayed_until);
    }

    public function testNotDelayedIfNotifiableImplementsDelayUntilMethodWithNow(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::parse('2019-09-10 12:51:00'));
        $notifiable = new class() extends Notifiable {
            protected $attributes = ['id' => 54321];

            public function delayNotificationsUntil()
            {
                return Carbon::now();
            }
        };
        $notification = new DummyThrottledNotification();

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, ThrottledNotification::whereNull('delayed_until')->count());
    }

    public function testNotDelayedIfNotifiableImplementsDelayUntilMethodWithNull(): void
    {
        // arrange
        $notifiable = new class() extends Notifiable {
            protected $attributes = ['id' => 54321];

            public function delayNotificationsUntil(): void
            {
            }
        };
        $notification = new DummyThrottledNotification();

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, ThrottledNotification::whereNull('delayed_until')->count());
    }

    public function testNotDelayedIfNotifiableImplementsDelayUntilMethodWithPastDate(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::parse('2019-09-10 12:51:00'));
        $notifiable = new class() extends Notifiable {
            protected $attributes = ['id' => 54321];

            public function delayNotificationsUntil()
            {
                return Carbon::now()->subMinute();
            }
        };
        $notification = new DummyThrottledNotification();

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, ThrottledNotification::whereNull('delayed_until')->count());
    }
}
