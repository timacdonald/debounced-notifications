<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Notifiable;
use TiMacDonald\ThrottledNotifications\Queries\Reservables;
use TiMacDonald\ThrottledNotifications\Models\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;

class ReservablesTest extends TestCase
{
    public function testNotificationsAreReserved(): void
    {
        // arrange
        $notification = \factory(ThrottledNotification::class)->create();
        \assert($notification instanceof ThrottledNotification);

        // guard assert
        $this->assertNull($notification->reserved_key);

        // act
        $count = $this->reservables()->reserve($notification->databaseNotification->notifiable, 'xxxx');

        // assert
        $notification = $notification->refresh();
        $this->assertSame(1, $count);
        $this->assertSame('xxxx', $notification->reserved_key);
    }

    public function testNotificationsForOtherNotifiablesAreIgnored(): void
    {
        // arrange
        $notification = \factory(ThrottledNotification::class)->create();
        \assert($notification instanceof ThrottledNotification);
        $notifiable = \factory(Notifiable::class)->create();
        \assert($notifiable instanceof Notifiable);

        // guard assert
        $this->assertNull($notification->reserved_key);

        // act
        $count = $this->reservables()->reserve($notifiable, 'xxxx');

        // assert
        $notification = $notification->refresh();
        $this->assertSame(0, $count);
        $this->assertNull($notification->reserved_key);
    }

    public function testDelayedNotificationsAreIgnored(): void
    {
        // arrange
        $notification = \factory(ThrottledNotification::class)->states(['delayed'])->create();
        \assert($notification instanceof ThrottledNotification);

        // guard assert
        $this->assertNull($notification->reserved_key);

        // act
        $count = $this->reservables()->reserve($notification->databaseNotification->notifiable, 'xxxx');

        // assert
        $notification = $notification->refresh();
        $this->assertSame(0, $count);
        $this->assertNull($notification->reserved_key);
    }

    public function testSentNotificationsAreIgnored(): void
    {
        // arrange
        $notification = \factory(ThrottledNotification::class)->states(['sent'])->create();
        \assert($notification instanceof ThrottledNotification);

        // guard assert
        $this->assertNull($notification->reserved_key);

        // act
        $count = $this->reservables()->reserve($notification->databaseNotification->notifiable, 'xxxx');

        // assert
        $notification = $notification->refresh();
        $this->assertSame(0, $count);
        $this->assertNull($notification->reserved_key);
    }

    public function testReadNotificationAreIgnored(): void
    {
        // arrange
        $databaseNotification = \factory(DatabaseNotification::class)->states(['read'])->create();
        \assert($databaseNotification instanceof DatabaseNotification);
        $throttledNotification = \factory(ThrottledNotification::class)->create([
            'notification_id' => $databaseNotification->id,
        ]);
        \assert($throttledNotification instanceof ThrottledNotification);

        // guard assert
        $this->assertNull($throttledNotification->reserved_key);

        // act
        $count = $this->reservables()->reserve($databaseNotification->notifiable, 'xxxx');

        // assert
        $throttledNotification = $throttledNotification->refresh();
        $this->assertSame(0, $count);
        $this->assertNull($throttledNotification->reserved_key);
    }

    public function testReservedNotificationsAreIgnored(): void
    {
        // arrange
        $notification = \factory(ThrottledNotification::class)->states(['reserved'])->create();
        \assert($notification instanceof ThrottledNotification);

        // guard assert
        $this->assertNotNull($notification->reserved_key);

        // act
        $count = $this->reservables()->reserve($notification->databaseNotification->notifiable, 'xxxx');

        // assert
        $notification = $notification->refresh();
        $this->assertSame(0, $count);
        $this->assertNotNull($notification->reserved_key);
        $this->assertNotSame('xxxx', $notification->reserved_key);
    }

    public function testNotificationsWithDifferentKeyAreNotReleased(): void
    {
        // arrange
        $notification = \factory(ThrottledNotification::class)->states(['reserved'])->create();
        \assert($notification instanceof ThrottledNotification);

        // guard assert
        $this->assertNotNull($notification->reserved_key);

        // act
        $count = $this->reservables()->release('xxxx');

        // assert
        $notification = $notification->refresh();
        $this->assertSame(0, $count);
        $this->assertNotNull($notification->reserved_key);
        $this->assertNotSame('xxxx', $notification->reserved_key);
    }

    public function testNotificationsAreReleased(): void
    {
        // arrange
        $notification = \factory(ThrottledNotification::class)->states(['reserved'])->create();
        \assert($notification instanceof ThrottledNotification);

        // guard assertion
        $this->assertNotNull($notification->reserved_key);

        // act
        $count = $this->reservables()->release($notification->reserved_key);

        // assert
        $notification = $notification->refresh();
        $this->assertSame(1, $count);
        $this->assertNull($notification->reserved_key);
    }

    public function testReservedNotificationsAreRetrieved(): void
    {
        // arrange
        $notification = \factory(ThrottledNotification::class)->states(['reserved'])->create();
        \assert($notification instanceof ThrottledNotification);

        // guard assertion
        $this->assertIsString($notification->reserved_key);

        // act
        $notifications = $this->reservables()->get($notification->reserved_key);

        // assert
        $this->assertCount(1, $notifications);
    }

    public function testReservedNotificationWithAnotherKeyAreNotRetrieved(): void
    {
        // arrange
        \factory(ThrottledNotification::class)->states(['reserved'])->create();

        // act
        $notifications = $this->reservables()->get('xxxx');

        // assert
        $this->assertCount(0, $notifications);
    }

    private function reservables(): Reservables
    {
        $reservables = $this->app[Reservables::class];

        \assert($reservables instanceof Reservables);

        return $reservables;
    }
}
