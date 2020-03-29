<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Builders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use TiMacDonald\ThrottledNotifications\Notifiable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class DatabaseNotificationBuilder extends EloquentBuilder
{
    public function whereUnread(): self
    {
        $this->query->whereNull('read_at');

        return $this;
    }

    public function groupByNotifiable(): self
    {
        $this->groupBy(['notifiable_type', 'notifiable_id']);

        return $this;
    }

    public function orderByOldest(): self
    {
        $this->oldest('notifications.created_at');

        return $this;
    }

    public function selectNotifiable(): self
    {
        $this->select([
            'notifications.notifiable_id as '.Notifiable::KEY_ATTRIBUTE,
            'notifications.notifiable_type as '.Notifiable::TYPE_ATTRIBUTE,
        ]);

        return $this;
    }

    public function joinThrottledNotifications(ThrottledNotificationBuilder $throttledNotifications): self
    {
        $baseQuery = $throttledNotifications->toBase();

        $this->join('throttled_notifications', static function (JoinClause $join) use ($baseQuery): void {
            $join->on('notifications.id', 'throttled_notifications.notification_id')
                ->mergeWheres($baseQuery->wheres, $baseQuery->bindings);
        });

        return $this;
    }

    public function whereNotifiable(Model $notifiable): self
    {
        // TODO: does this need to account for morph map keys?

        $this->where('notifiable_type', '=', \get_class($notifiable))
            ->where('notifiable_id', '=', $notifiable->getKey());

        return $this;
    }
}
