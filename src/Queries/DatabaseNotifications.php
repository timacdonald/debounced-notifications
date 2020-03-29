<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Queries;

use TiMacDonald\ThrottledNotifications\Models\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\Builders\DatabaseNotificationBuilder;

class DatabaseNotifications
{
    public function query(): DatabaseNotificationBuilder
    {
        return DatabaseNotification::query()
            ->whereUnread();
    }
}
