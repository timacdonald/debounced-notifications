<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Tests\DummyThrottledNotification;
use TiMacDonald\ThrottledNotifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\ThrottledNotification;

$factory->define(ThrottledNotification::class, function (Faker $faker) {
    return [
        'payload' => new DummyThrottledNotification,
        'notification_id' => factory(DatabaseNotification::class),
    ];
});

$factory->state(ThrottledNotification::class, 'sent', function (Faker $faker) {
    return [
        'sent_at' => $faker->dateTime,
    ];
});

