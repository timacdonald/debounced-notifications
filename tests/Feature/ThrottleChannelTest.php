<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Notifiable;
use Tests\Notification;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;

class ThrottleChannelTest extends TestCase
{
    public function testDatabaseNotificationIsCreated(): void
    {
        // arrange
        $notifiable = \factory(Notifiable::class)->create();
        \assert($notifiable instanceof Notifiable);
        $notification = new Notification();

        // act
        $this->channelManager()->send($notifiable, $notification);

        // assert
        $this->assertSame(1, DatabaseNotification::query()->count());
    }

    public function testThrottledNotificationIsCreated(): void
    {
        // arrange
        $notifiable = \factory(Notifiable::class)->create();
        \assert($notifiable instanceof Notifiable);
        $notification = new Notification();

        // act
        $this->channelManager()->send($notifiable, $notification);

        // assert
        $this->assertSame(1, ThrottledNotification::query()->count());
    }

    private function channelManager(): ChannelManager
    {
        $channelManager = $this->app[ChannelManager::class];

        \assert($channelManager instanceof ChannelManager);

        return $channelManager;
    }
}
