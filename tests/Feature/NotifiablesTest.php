<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use Tests\Notifiable;
use TiMacDonald\CallableFake\CallableFake;
use TiMacDonald\ThrottledNotifications\Contracts\Notifiables;
use TiMacDonald\ThrottledNotifications\Models\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;

class NotifiablesTest extends TestCase
{
    public function testNotificationsAfterWaitTimeHasLaspedAreIncluded(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        $throttledNotification = \factory(ThrottledNotification::class)->create();
        \assert($throttledNotification instanceof ThrottledNotification);
        Carbon::setTestNow(Carbon::now()->addMinutes(10));
        $callable = new CallableFake();

        // act
        $this->notifiables()->each($callable);

        // assert
        $callable->assertCalledTimes(static function (Notifiable $notifiable) use ($throttledNotification): bool {
            return $notifiable->is($throttledNotification->databaseNotification->notifiable);
        }, 1);
    }

    public function testNotificationsBeforeWaitTimeHasLaspedAreIgnored(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        \factory(ThrottledNotification::class)->create();
        Carbon::setTestNow(Carbon::now()->addMinutes(10)->subSecond());
        $callable = new CallableFake();

        // act
        $this->notifiables()->each($callable);

        // assert
        $callable->assertNotInvoked();
    }

    public function testOnlyIncludesOnePerNotifiable(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        $notifiable = \factory(Notifiable::class)->create();
        \assert($notifiable instanceof Notifiable);
        $scenario = static function () use ($notifiable): void {
            $notification = \factory(DatabaseNotification::class)->create([
                'notifiable_id' => $notifiable->id,
            ]);
            \assert($notification instanceof DatabaseNotification);
            \factory(ThrottledNotification::class)->create([
                'notification_id' => $notification->id,
            ]);
        };
        $scenario();
        $scenario();
        Carbon::setTestNow(Carbon::now()->addMinutes(10));
        $callable = new CallableFake();

        // act
        $this->notifiables()->each($callable);

        // assert
        $callable->assertCalledTimes(static function (Notifiable $received) use ($notifiable): bool {
            return $received->is($notifiable);
        }, 1);
    }

    public function testIncludesMultipleNotifiable(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        $scenario = static function (): DatabaseNotification {
            $notification = \factory(DatabaseNotification::class)->create();
            \assert($notification instanceof DatabaseNotification);
            \factory(ThrottledNotification::class)->create([
                'notification_id' => $notification->id,
            ]);

            return $notification;
        };
        $first = $scenario();
        $second = $scenario();
        Carbon::setTestNow(Carbon::now()->addMinutes(10));
        $callable = new CallableFake();

        // act
        $this->notifiables()->each($callable);

        // assert
        $callable->assertTimesInvoked(2);
        $callable->assertCalled(static function (Notifiable $notifiable) use ($first): bool {
            return $notifiable->is($first->notifiable);
        });
        $callable->assertCalled(static function (Notifiable $notifiable) use ($second): bool {
            return $notifiable->is($second->notifiable);
        });
    }

    public function testReadNotificationAreIgnored(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        $databaseNotification = \factory(DatabaseNotification::class)->states(['read'])->create();
        \assert($databaseNotification instanceof DatabaseNotification);
        \factory(ThrottledNotification::class)->create([
            'notification_id' => $databaseNotification->id,
        ]);
        Carbon::setTestNow(Carbon::now()->addMinutes(10));
        $callable = new CallableFake();

        // act
        $this->notifiables()->each($callable);

        // assert
        $callable->assertNotInvoked();
    }

    public function testSentNotificationsAreIgnored(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        \factory(ThrottledNotification::class)->states(['sent'])->create();
        Carbon::setTestNow(Carbon::now()->addMinutes(10));
        $callable = new CallableFake();

        // act
        $this->notifiables()->each($callable);

        // assert
        $callable->assertNotInvoked();
    }

    public function testReservedNotificationsAreIgnored(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        \factory(ThrottledNotification::class)->states(['reserved'])->create();
        Carbon::setTestNow(Carbon::now()->addMinutes(10));
        $callable = new CallableFake();

        // act
        $this->notifiables()->each($callable);

        // assert
        $callable->assertNotInvoked();
    }

    private function notifiables(): Notifiables
    {
        $notifiables = $this->app[Notifiables::class];

        \assert($notifiables instanceof Notifiables);

        return $notifiables;
    }
}
