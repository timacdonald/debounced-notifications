<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Queries;

use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;
use TiMacDonald\ThrottledNotifications\Builders\ThrottledNotificationBuilder;

class ThrottledNotifications
{
    public function query(): ThrottledNotificationBuilder
    {
        return ThrottledNotification::query()
            ->whereUnsent()
            ->whereNotDelayed()
            ->whereUnreserved();
    }
}
