<?php

declare(strict_types=1);

namespace Tests;

use TiMacDonald\ThrottledNotifications\ThrottledNotification;

class ThrottledNotificationTest extends TestCase
{
    public function testPayloadIsSerializedAndDeserialized(): void
    {
        // arrange
        $notification = new ThrottledNotificationTestDummyNotification('expected value');
        $throttledNotification = new ThrottledNotification();

        // act
        $throttledNotification->payload = $notification;

        // assert
        $this->assertNotSame($notification, $throttledNotification->payload);
        $this->assertSame('expected value', $throttledNotification->payload->constructorArgs[0]);
    }
}

class ThrottledNotificationTestDummyNotification extends DummyThrottledNotification
{
    public $constructorArgs;

    public function __construct()
    {
        $this->constructorArgs = \func_get_args();
    }
}
