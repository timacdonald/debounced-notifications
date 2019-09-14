<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Queries;

use Illuminate\Database\Eloquent\Builder;
use TiMacDonald\ThrottledNotifications\Models\DatabaseNotification;

class DatabaseNotifications
{
    public function query(): Builder
    {
        return DatabaseNotification::query()
            ->whereUnread();
    }
}
