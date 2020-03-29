<?php

declare(strict_types=1);

use Tests\Notifiable;

\assert($factory instanceof \Illuminate\Database\Eloquent\Factory);

$factory->define(Notifiable::class, static function () {
    return [
    ];
});
