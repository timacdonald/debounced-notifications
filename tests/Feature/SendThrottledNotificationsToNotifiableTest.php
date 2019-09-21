<?php

declare(strict_types=1);

namespace Tests\Feature;

use Exception;
use Tests\TestCase;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;
use TiMacDonald\ThrottledNotifications\Jobs\SendThrottledNotificationsToNotifiable;

class SendThrottledNotificationsToNotifiableTest extends TestCase
{
    public function testNotificationsAreReserved(): void
    {
        // arrange
        $notification = \factory(ThrottledNotification::class)->create();

        // act
        $job = new SendThrottledNotificationsToNotifiable($notification->databaseNotification->notifiable, 'expected-key');
        $this->app->call([$job, 'handle']);

        // assert
        $this->assertSame('expected-key', $notification->fresh()->reserved_key);
    }

    public function testWhenNotificationsAreReleasedWhenJobFails(): void
    {
        // arrange
        $notification = \factory(ThrottledNotification::class)->states(['reserved'])->create([
            'reserved_key' => 'reserved-key',
        ]);

        // act
        $job = new SendThrottledNotificationsToNotifiable($notification->databaseNotification->notifiable, 'reserved-key');
        $job->failed(new Exception());

        // assert
        $this->assertNull($notification->fresh()->reserved_key);
    }
}
