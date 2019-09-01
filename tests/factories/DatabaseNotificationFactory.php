<?php

use Faker\Generator as Faker;

use Tests\Notifiable;
use TiMacDonald\ThrottledNotifications\DatabaseNotification;

$factory->define(DatabaseNotification::class, function (Faker $faker) {
    return [
        'id' => $faker->unique()->uuid,
        'type' => '',
        'notifiable_type' => Notifiable::class,
        'notifiable_id' => factory(Notifiable::class)->create()->id,
        'data' => '{}',
    ];
});

$factory->state(DatabaseNotification::class, 'read', function (Faker $faker) {
    return [
        'read_at' => $faker->dateTime,
    ];
});
