<?php

use Faker\Generator as Faker;

use TiMacDonald\ThrottledNotifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\ThrottledNotification;

$factory->define(ThrottledNotification::class, function (Faker $faker) {
    return [
        'notification_id' => factory(DatabaseNotification::class),
    ];
});

$factory->state(ThrottledNotification::class, 'sent', function (Faker $faker) {
    return [
        'sent_at' => $faker->dateTime,
    ];
});
