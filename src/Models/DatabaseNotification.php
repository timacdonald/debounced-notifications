<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification as BaseDatabaseNotification;

class DatabaseNotification extends BaseDatabaseNotification
{
    public function scopeWhereNotifiable(Builder $builder, Model $notifiable): void
    {
        $builder->where('notifiable_type', '=', \get_class($notifiable))
            ->where('notifiable_id', '=', $notifiable->getKey());
    }

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
