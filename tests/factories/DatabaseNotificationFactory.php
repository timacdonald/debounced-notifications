<?php

declare(strict_types=1);

use Tests\Notifiable;
use Faker\Generator as Faker;
use TiMacDonald\ThrottledNotifications\DatabaseNotification;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(DatabaseNotification::class, static function (Faker $faker) {
    return [
        'id' => $faker->unique()->uuid,
        'type' => '',
        'notifiable_type' => Notifiable::class,
        'notifiable_id' => \factory(Notifiable::class)->create()->id,
        'data' => '{}',
    ];
});

$factory->state(DatabaseNotification::class, 'read', static function (Faker $faker) {
    return [
        'read_at' => $faker->dateTime,
    ];
});
