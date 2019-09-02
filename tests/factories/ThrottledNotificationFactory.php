<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Tests\DummyThrottledNotification;
use TiMacDonald\ThrottledNotifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\ThrottledNotification;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(ThrottledNotification::class, static function (Faker $faker) {
    return [
        'payload' => new DummyThrottledNotification(),
        'notification_id' => factory(DatabaseNotification::class),
    ];
});

$factory->state(ThrottledNotification::class, 'sent', static function (Faker $faker) {
    return [
        'sent_at' => $faker->dateTime,
    ];
});
