<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use Tests\Notifiable;
use Tests\NotifiablesCallable;
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
        $callable = new NotifiablesCallable();

        // act
        $this->notifiables()->each($callable);

        // assert
        $this->assertCount(1, $callable->received);
        $this->assertTrue($callable->received[0]->is($throttledNotification->databaseNotification->notifiable));
    }

    public function testNotificationsBeforeWaitTimeHasLaspedAreIgnored(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        \factory(ThrottledNotification::class)->create();
        Carbon::setTestNow(Carbon::now()->addMinutes(10)->subSecond());
        $callable = new NotifiablesCallable();

        // act
        $this->notifiables()->each($callable);

        // assert
        $this->assertCount(0, $callable->received);
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
        $callable = new NotifiablesCallable();

        // act
        $this->notifiables()->each($callable);

        // assert
        $this->assertCount(1, $callable->received);
        $this->assertTrue($callable->received[0]->is($notifiable));
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
        $callable = new NotifiablesCallable();

        // act
        $this->notifiables()->each($callable);

        // assert
        $this->assertCount(2, $callable->received);
        $this->assertTrue($callable->received[0]->is($first->notifiable));
        $this->assertTrue($callable->received[1]->is($second->notifiable));
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
        $callable = new NotifiablesCallable();

        // act
        $this->notifiables()->each($callable);

        // assert
        $this->assertCount(0, $callable->received);
    }

    public function testSentNotificationsAreIgnored(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        \factory(ThrottledNotification::class)->states(['sent'])->create();
        Carbon::setTestNow(Carbon::now()->addMinutes(10));
        $callable = new NotifiablesCallable();

        // act
        $this->notifiables()->each($callable);

        // assert
        $this->assertCount(0, $callable->received);
    }

    public function testReservedNotificationsAreIgnored(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        \factory(ThrottledNotification::class)->states(['reserved'])->create();
        Carbon::setTestNow(Carbon::now()->addMinutes(10));
        $callable = new NotifiablesCallable();

        // act
        $this->notifiables()->each($callable);

        // assert
        $this->assertCount(0, $callable->received);
    }

    private function notifiables(): Notifiables
    {
        $notifiables = $this->app[Notifiables::class];

        \assert($notifiables instanceof Notifiables);

        return $notifiables;
    }
}
