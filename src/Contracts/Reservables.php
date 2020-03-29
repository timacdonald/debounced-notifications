<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Contracts;

use Illuminate\Database\Eloquent\Model;
use TiMacDonald\ThrottledNotifications\ThrottledNotificationCollection;

interface Reservables
{
    public function reserve(Model $notifiable, string $key): int;

    public function release(string $key): int;

    public function get(string $key): ThrottledNotificationCollection;

    public function markAsSent(string $key): int;
}
