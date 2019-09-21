<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use TiMacDonald\ThrottledNotifications\ThrottledNotificationCollection;

interface Reservables
{
    public function query(Model $notifiable): Builder;

    public function release(string $key): void;

    public function get(string $key): ThrottledNotificationCollection;

    public function markAsSent(string $key): void;
}
