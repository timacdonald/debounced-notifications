<?php

use Faker\Generator as Faker;

use TiMacDonald\ThrottledNotifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\ShouldThrottle;
use TiMacDonald\ThrottledNotifications\ThrottledNotification;

$factory->define(ThrottledNotification::class, function (Faker $faker) {
    return [
        'payload' => new DummyPayload,
        'notification_id' => factory(DatabaseNotification::class),
    ];
});

$factory->state(ThrottledNotification::class, 'sent', function (Faker $faker) {
    return [
        'sent_at' => $faker->dateTime,
    ];
});

if (! class_exists(DummyPayload::class)) {
    class DummyPayload implements ShouldThrottle
    {
        public function throttledVia($notifiable): array
        {
            return [];
        }
    }
}

