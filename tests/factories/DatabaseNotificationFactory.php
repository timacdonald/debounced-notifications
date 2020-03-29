<?php

declare(strict_types=1);

use Tests\Notifiable;
use Faker\Generator as Faker;
use TiMacDonald\ThrottledNotifications\Models\DatabaseNotification;

\assert($factory instanceof \Illuminate\Database\Eloquent\Factory);

$factory->define(DatabaseNotification::class, static function (Faker $faker) {
    return [
        'id' => $faker->unique()->uuid,
        'data' => '{}',
        'notifiable_id' => \factory(Notifiable::class),
        'notifiable_type' => Notifiable::class,
        'type' => '',
    ];
});

$factory->state(DatabaseNotification::class, 'read', static function (Faker $faker) {
    return [
        'read_at' => $faker->dateTime,
    ];
});
