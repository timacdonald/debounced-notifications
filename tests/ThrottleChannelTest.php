<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\ThrottleChannel;
use TiMacDonald\ThrottledNotifications\ThrottledNotification;

class ThrottleChannelTest extends TestCase
{
    public function test_database_notification_is_created()
    {
        // arrange
        $notifiable = factory(Notifiable::class)->create();
        $notification = new DummyThrottledNotification;

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, DatabaseNotification::count());
    }

    public function test_throttled_notification_is_created()
    {
        // arrange
        $notifiable = new DummyNotifiable(['id' => 1]);
        $notification = new DummyThrottledNotification;

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, ThrottledNotification::count());
    }

    public function test_not_delayed_if_notifiable_doesnt_implement_delay_until_method()
    {
        // arrange
        $notifiable = new DummyNotifiable(['id' => 1]);
        $notification = new DummyThrottledNotification;

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, ThrottledNotification::whereNotDelayed()->count());
    }

    public function test_delayed_if_notifiable_implements_delay_until_method_with_future_date()
    {
        // arrange
        Carbon::setTestNow(Carbon::parse('2019-09-10 12:51:00'));
        $notifiable = new class(['id' => 1]) extends DummyNotifiable {
            public function delayNotificationsUntil() {
                return now()->addDay();
            }
        };
        $notification = new DummyThrottledNotification;

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertCount(1, $notifications = ThrottledNotification::whereDelayed()->get());
        $this->assertSame('2019-09-11 12:51:00', $notifications[0]->delayed_until->format('Y-m-d H:i:s'));
    }

    public function test_not_delayed_if_notifiable_implements_delay_until_method_with_now()
    {
        // arrange
        Carbon::setTestNow(Carbon::parse('2019-09-10 12:51:00'));
        $notifiable = new class(['id' => 1]) extends DummyNotifiable {
            public function delayNotificationsUntil() {
                return now();
            }
        };
        $notification = new DummyThrottledNotification;

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, ThrottledNotification::whereNotDelayed()->count());
    }

    public function test_not_delayed_if_notifiable_implements_delay_until_method_with_past_date()
    {
        // arrange
        Carbon::setTestNow(Carbon::parse('2019-09-10 12:51:00'));
        $notifiable = new class(['id' => 1]) extends DummyNotifiable {
            public function delayNotificationsUntil() {
                return now()->subMinute();
            }
        };
        $notification = new DummyThrottledNotification;

        // act
        $this->app[ChannelManager::class]->send($notifiable, $notification);

        // assert
        $this->assertSame(1, ThrottledNotification::whereNotDelayed()->count());
    }
}
