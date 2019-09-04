<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification as BaseDatabaseNotification;

class DatabaseNotification extends BaseDatabaseNotification
{
    public function scopeWhereUnread(Builder $builder): void
    {
        $builder->whereNull('read_at');
    }

    public function scopeGroupByNotifiable(Builder $builder): void
    {
        $builder->groupBy(['notifiable_type', 'notifiable_id']);
    }

    public function scopeOrderByOldest(Builder $builder): void
    {
        $builder->oldest('notifications.created_at');
    }
}
