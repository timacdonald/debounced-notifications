<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use TiMacDonald\ThrottledNotifications\Delay;

class DelayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::now());
    }

    public function testDelayedIsNullIfNotifiableDoesntImplementDelayUntilMethod(): void
    {
        // arrange
        $notifiable = new class() extends Model {};

        // act
        $delay = Delay::until($notifiable);

        // assert
        $this->assertNull($delay);
    }

    public function testDelayIsExpectedDateIfNotifiableImplementsDelayUntilMethodWithFutureDate(): void
    {
        // arrange
        $notifiable = new class() extends Model {
            public function delayNotificationsUntil()
            {
                return Carbon::now()->addDay();
            }
        };

        // act
        $delay = Delay::until($notifiable);

        // assert
        $this->assertTrue(Carbon::now()->addDay()->eq($delay));
    }

    public function testDelayIsNotIfNotifiableImplementsDelayUntilMethodWithNow(): void
    {
        // arrange
        $notifiable = new class() extends Model {
            public function delayNotificationsUntil()
            {
                return Carbon::now();
            }
        };

        // act
        $delay = Delay::until($notifiable);

        // assert
        $this->assertNull($delay);
    }

    public function testDelayIsNullIfNotifiableImplementsDelayUntilMethodWithPastDate(): void
    {
        // arrange
        $notifiable = new class() extends Model {
            public function delayNotificationsUntil()
            {
                return Carbon::now()->subMinute();
            }
        };

        // act
        $delay = Delay::until($notifiable);

        // assert
        $this->assertNull($delay);
    }
}
