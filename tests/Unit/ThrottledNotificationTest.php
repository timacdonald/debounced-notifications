<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Notifications\Notification;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;

class ThrottledNotificationTest extends TestCase
{
    public function testPayloadIsSerializedAndDeserialized(): void
    {
        // arrange
        $notification = new DummyNotificationWithConstructorArgs('expected value');
        $throttledNotification = new ThrottledNotification();

        // act
        $throttledNotification->payload = $notification;

        // assert
        $this->assertNotSame($notification, $throttledNotification->payload);
        $this->assertSame('expected value', $throttledNotification->payload->constructorArgs[0]);
    }
}

class DummyNotificationWithConstructorArgs extends Notification
{
    /**
     * @var array
     */
    public $constructorArgs;

    public function __construct(...$args)
    {
        $this->constructorArgs = $args;
    }
}
