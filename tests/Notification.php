<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification as BaseNotification;
use TiMacDonald\ThrottledNotifications\Contracts\Throttleable;
use TiMacDonald\ThrottledNotifications\Throttleable as ThrottleableTrait;

class Notification extends BaseNotification implements Throttleable
{
    use ThrottleableTrait;

    public function throttledVia(Model $notifiable): array
    {
        return [];
    }
}
