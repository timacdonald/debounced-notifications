<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use TiMacDonald\ThrottledNotifications\Notifiable;
use Illuminate\Database\Query\Builder as QueryBuilder;
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

    public function scopeSelectNotifiable(Builder $builder): void
    {
        $builder->select([
            'notifications.notifiable_id as '.Notifiable::KEY_ATTRIBUTE,
            'notifications.notifiable_type as '.Notifiable::TYPE_ATTRIBUTE,
        ]);
    }

    public function scopeJoinThrottledNotifications(Builder $builder, QueryBuilder $throttledNotifications): void
    {
        $builder->join('throttled_notifications', static function (JoinClause $join) use ($throttledNotifications): void {
            $join->on('notifications.id', 'throttled_notifications.notification_id')
                ->mergeWheres($throttledNotifications->wheres, $throttledNotifications->bindings);
        });
    }

    public function scopeWhereNotifiable(Builder $builder, Model $notifiable): void
    {
        $builder->where('notifiable_type', '=', \get_class($notifiable))
            ->where('notifiable_id', '=', $notifiable->getKey());
    }
}
