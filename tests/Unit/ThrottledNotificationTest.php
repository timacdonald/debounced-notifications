<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\Notification;
use PHPUnit\Framework\TestCase;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;

class ThrottledNotificationTest extends TestCase
{
    public function testPayloadIsSerializedAndDeserialized(): void
    {
        // arrange
        $notification = new Notification();
        $notification->id = 'expected id';
        $throttledNotification = new ThrottledNotification();

        // act
        $throttledNotification->payload = $notification;

        // assert
        $this->assertNotSame($notification, $throttledNotification->payload);
        $this->assertSame('expected id', $throttledNotification->payload->id);
    }
}
