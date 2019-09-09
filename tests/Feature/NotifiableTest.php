<?php

declare(strict_types=1);

namespace Tests\Feature;

use stdClass;
use Tests\TestCase;
use Tests\Notifiable as DummyNotifiable;
use TiMacDonald\ThrottledNotifications\Notifiable;

class NotifiableTest extends TestCase
{
    public function testHydration(): void
    {
        // arrange
        $expected = \factory(DummyNotifiable::class)->create();
        $notifiable = new stdClass();
        $notifiable->key = $expected->id;
        $notifiable->type = DummyNotifiable::class;

        // act
        $instance = Notifiable::hydrate($notifiable);

        // assert
        $this->assertTrue($expected->is($instance));
    }
}
