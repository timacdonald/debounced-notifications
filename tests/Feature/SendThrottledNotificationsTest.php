<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\Bus;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;
use TiMacDonald\ThrottledNotifications\Jobs\SendThrottledNotifications;
use TiMacDonald\ThrottledNotifications\Jobs\SendThrottledNotificationsToNotifiable;

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
