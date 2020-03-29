<?php

declare(strict_types=1);

use Tests\Notification;
use Faker\Generator as Faker;
use TiMacDonald\ThrottledNotifications\Models\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;

\assert($factory instanceof \Illuminate\Database\Eloquent\Factory);

$factory->define(ThrottledNotification::class, static function () {
    return [
        'notification_id' => \factory(DatabaseNotification::class),
        'payload' => new Notification(),
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
