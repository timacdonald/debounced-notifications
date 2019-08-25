<?php

use Faker\Generator as Faker;

use TiMacDonald\ThrottledNotifications\DatabaseNotification;

$factory->define(DatabaseNotification::class, function (Faker $faker) {
    return [
        'id' => $faker->unique()->uuid,
        'type' => '',
        'notifiable_type' => 'NotificationClass',
        'notifiable_id' => $faker->unique()->uuid,
        'data' => '{}',
    ];
});

$factory->state(DatabaseNotification::class, 'read', function (Faker $faker) {
    return [
        'read_at' => $faker->dateTime,
    ];
});
