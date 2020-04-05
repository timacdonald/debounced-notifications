<?php

declare(strict_types=1);

namespace Tests\Feature;

use Exception;
use Tests\TestCase;
use Tests\Notifiable;
use Tests\CourierFake;
use TiMacDonald\ThrottledNotifications\Contracts\Courier;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;
use TiMacDonald\ThrottledNotifications\Jobs\SendThrottledNotificationsToNotifiable;

class SendThrottledNotificationsToNotifiableTest extends TestCase
{
    public function testNotificationsAreReserved(): void
    {
        // arrange
        $this->app->bind(Courier::class, CourierFake::class);
        $notification = \factory(ThrottledNotification::class)->create();
        \assert($notification instanceof ThrottledNotification);

        // act
        $job = new SendThrottledNotificationsToNotifiable($notification->databaseNotification->notifiable, 'expected-key');
        $this->app->call([$job, 'handle']);

        // assert
        $notification->refresh();
        $this->assertSame('expected-key', $notification->reserved_key);
    }

    public function testWhenNotificationsAreReleasedWhenJobFails(): void
    {
        // arrange
        $this->app->bind(Courier::class, CourierFake::class);
        $notification = \factory(ThrottledNotification::class)->states(['reserved'])->create([
            'reserved_key' => 'reserved-key',
        ]);
        \assert($notification instanceof ThrottledNotification);

        // act
        $job = new SendThrottledNotificationsToNotifiable($notification->databaseNotification->notifiable, 'reserved-key');
        $job->failed(new Exception());

        // assert
        $notification->refresh();
        $this->assertNull($notification->reserved_key);
    }

    public function testNotificationsAreMarkedAsSent(): void
    {
        // arrange
        $this->app->bind(Courier::class, CourierFake::class);
        $notification = \factory(ThrottledNotification::class)->create();
        \assert($notification instanceof ThrottledNotification);

        // guard assert
        $this->assertNull($notification->sent_at);

        // act
        $job = new SendThrottledNotificationsToNotifiable($notification->databaseNotification->notifiable, 'xxxx');
        $this->app->call([$job, 'handle']);

        // assert
        $notification = $notification->refresh();
        $this->assertNotNull($notification->sent_at);
    }

    public function testBailsWhenNotReservablesAreFound(): void
    {
        // arrange
        $courier = new CourierFake();
        $this->app->instance(Courier::class, $courier);
        $notifiable = \factory(Notifiable::class)->create();
        \assert($notifiable instanceof Notifiable);

        // act
        $job = new SendThrottledNotificationsToNotifiable($notifiable, 'xxxx');
        $this->app->call([$job, 'handle']);

        // assert
        $courier->assertNothingSent();
    }

    public function testCourierSendsEmailToNotifiable(): void
    {
        // arrange
        $courier = new CourierFake();
        $this->app->instance(Courier::class, $courier);
        $this->markTestIncomplete();
    }
}
