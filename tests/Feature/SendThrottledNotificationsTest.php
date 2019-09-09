<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\Bus;
use TiMacDonald\ThrottledNotifications\ThrottledNotification;
use TiMacDonald\ThrottledNotifications\SendThrottledNotifications;
use TiMacDonald\ThrottledNotifications\SendThrottledNotificationsToNotifiable;

class SendThrottledNotificationsTest extends TestCase
{
    public function testDispatchesJobsForMultipleNotifiables(): void
    {
        // arrange
        Bus::fake();
        \factory(ThrottledNotification::class)->create();
        \factory(ThrottledNotification::class)->create();
        Carbon::setTestNow(Carbon::now()->addMinutes(10));

        // act
        $this->app->call([new SendThrottledNotifications(), 'handle']);

        // assert
        Bus::assertDispatched(SendThrottledNotificationsToNotifiable::class, 2);
    }
}
