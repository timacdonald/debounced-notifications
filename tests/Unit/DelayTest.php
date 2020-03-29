<?php

declare(strict_types=1);

namespace Tests\Unit;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Model;
use TiMacDonald\ThrottledNotifications\Delay;

class DelayTest extends TestCase
{
    public function testDelayedIsNullIfNotifiableDoesntImplementDelayUntilMethod(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        $notifiable = new class() extends Model {
        };

        // act
        $delay = (new Delay())->until($notifiable);

        // assert
        $this->assertNull($delay);
    }

    public function testDelayIsExpectedDateIfNotifiableImplementsDelayUntilMethodWithFutureDate(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        $notifiable = new class() extends Model {
            public function delayNotificationsUntil(): Carbon
            {
                return Carbon::now()->addDay();
            }
        };

        // act
        $delay = (new Delay())->until($notifiable);

        // assert
        $this->assertTrue(Carbon::now()->addDay()->eq($delay));
    }

    public function testDelayIsNotIfNotifiableImplementsDelayUntilMethodWithNow(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        $notifiable = new class() extends Model {
            public function delayNotificationsUntil(): Carbon
            {
                return Carbon::now();
            }
        };

        // act
        $delay = (new Delay())->until($notifiable);

        // assert
        $this->assertNull($delay);
    }

    public function testDelayIsNullIfNotifiableImplementsDelayUntilMethodWithPastDate(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        $notifiable = new class() extends Model {
            public function delayNotificationsUntil(): Carbon
            {
                return Carbon::now()->subMinute();
            }
        };

        // act
        $delay = (new Delay())->until($notifiable);

        // assert
        $this->assertNull($delay);
    }
}
