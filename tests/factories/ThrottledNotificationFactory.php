<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Tests\DummyThrottledNotification;
use TiMacDonald\ThrottledNotifications\Models\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;

$factory->define(ThrottledNotification::class, static function (Faker $faker) {
    return [
        'payload' => new DummyThrottledNotification(),
        'notification_id' => \factory(DatabaseNotification::class),
    ];
});

$factory->state(ThrottledNotification::class, 'sent', static function (Faker $faker) {
    return [
        'sent_at' => $faker->dateTime,
    ];
});

$factory->state(ThrottledNotification::class, 'delayed', static function (Faker $faker) {
    return [
        'delayed_until' => $faker->dateTime,
    ];
});

$factory->state(ThrottledNotification::class, 'reserved', static function (Faker $faker) {
    return [
        'reserved_key' => $faker->uuid,
    ];
});
