<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Queries;

use Illuminate\Database\Eloquent\Builder;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;

class ThrottledNotifications
{
    public function query(): Builder
    {
        return ThrottledNotification::query()
            ->whereUnsent()
            ->whereNotDelayed()
            ->whereUnreserved();
    }
}
