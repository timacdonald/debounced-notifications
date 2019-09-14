<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Notifiable;
use TiMacDonald\ThrottledNotifications\Models\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;
use TiMacDonald\ThrottledNotifications\Queries\Reservables;

class ReservablesTest extends TestCase
{
    public function testNotificationsAreInlcuded(): void
    {
        // arrange
        $notification = factory(ThrottledNotification::class)->create();

        // act
        $notifications = $this->app[Reservables::class]->query($notification->databaseNotification->notifiable)->get();

        // assert
        $this->assertCount(1, $notifications);
    }

    public function testNotificationsForOtherNotifiablesAreIgnored(): void
    {
        // arrange
        $notification = factory(ThrottledNotification::class)->create();
        $notifiable = factory(Notifiable::class)->create();

        // act
        $notifications = $this->app[Reservables::class]->query($notifiable)->get();

        // assert
        $this->assertCount(0, $notifications);
    }

    public function testDelayedNotificationsAreIgnored(): void
    {
        // arrange
        $notification = \factory(ThrottledNotification::class)->states(['delayed'])->create();

        // act
        $reservables = $this->app[Reservables::class]->query($notification->databaseNotification->notifiable)->get();

        // assert
        $this->assertCount(0, $reservables);
    }

    public function testSentNotificationsAreIgnored(): void
    {
        // arrange
        $notification = \factory(ThrottledNotification::class)->states(['sent'])->create();

        // act
        $reservables = $this->app[Reservables::class]->query($notification->databaseNotification->notifiable)->get();

        // assert
        $this->assertCount(0, $reservables);
    }

    public function testReadNotificationAreIgnored(): void
    {
        // arrange
        $databaseNotification = factory(DatabaseNotification::class)->states(['read'])->create();
        \factory(ThrottledNotification::class)->create([
            'notification_id' => $databaseNotification->id,
        ]);

        // act
        $reservables = $this->app[Reservables::class]->query($databaseNotification->notifiable)->get();

        // assert
        $this->assertCount(0, $reservables);
    }

    public function testReservedNotificationsAreIgnored(): void
    {
        // arrange
        $notification = \factory(ThrottledNotification::class)->states(['reserved'])->create();

        // act
        $reservables = $this->app[Reservables::class]->query($notification->databaseNotification->notifiable)->get();

        // assert
        $this->assertCount(0, $reservables);
    }

    public function testNotificationsWithDifferentKeyAreNotReleased(): void
    {
        // arrange
        $notification = factory(ThrottledNotification::class)->states(['reserved'])->create();

        // act
        $notifications = $this->app[Reservables::class]->release('xxxx');

        // assert
        $this->assertNotNull($notification->fresh()->reserved_key);
    }

    public function testNotificationsAreReleased(): void
    {
        // arrange
        $notification = factory(ThrottledNotification::class)->states(['reserved'])->create();

        // act
        $notifications = $this->app[Reservables::class]->release($notification->reserved_key);

        // assert
        $this->assertNull($notification->fresh()->reserved_key);
    }

    public function testReservedNotificationsAreRetrieved(): void
    {
        // arrange
        $notification = factory(ThrottledNotification::class)->states(['reserved'])->create();

        // act
        $notifications = $this->app[Reservables::class]->get($notification->reserved_key);

        // assert
        $this->assertCount(1, $notifications);
    }

    public function testReservedNotificationWithAnotherKeyAreNotRetrieved(): void
    {
        // arrange
        $notification = factory(ThrottledNotification::class)->states(['reserved'])->create();

        // act
        $notifications = $this->app[Reservables::class]->get('xxxx');

        // assert
        $this->assertCount(0, $notifications);
    }
}
