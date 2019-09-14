<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Notifiable;
use TiMacDonald\ThrottledNotifications\Contracts\Reservables;
use TiMacDonald\ThrottledNotifications\Jobs\SendThrottledNotificationsToNotifiable;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;

class SendThrottledNotificationsToNotifiableTest extends TestCase
{
    public function testNotificationsAreReserved(): void
    {
        // arrange
        $notification = factory(ThrottledNotification::class)->create();

        // act
        $this->app->call([new SendThrottledNotificationsToNotifiable($notification->databaseNotification->notifiable, 'expected-key'), 'handle']);

        // assert
        $this->assertSame('expected-key', $notification->fresh()->reserved_key);
    }

    public function testWhenNotificationsAreReleasedWhenJobFails(): void
    {
        //
    }
}
