<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Queries;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use TiMacDonald\ThrottledNotifications\Models\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\Contracts\ThrottledNotifications;
use TiMacDonald\ThrottledNotifications\Contracts\Notifiables as NotifiablesContract;

class Notifiables implements NotifiablesContract
{
    /**
     * \TiMacDonald\ThrottledNotifications\Contracts\ThrottledNotifications.
     */
    private $throttledNotifications;

    public function __construct(ThrottledNotifications $throttledNotifications)
    {
        $this->throttledNotifications = $throttledNotifications;
    }

    public function query(): Builder
    {
        return $this->databaseNotifications()
            ->join(...$this->join())
            ->select([
                'notifications.notifiable_id as key',
                'notifications.notifiable_type as type',
            ]);
    }

    private function databaseNotifications(): Builder
    {
        return DatabaseNotification::query()
            ->whereUnread()
            ->orderByOldest()
            ->groupByNotifiable()
            ->toBase();
    }

    private function join(): array
    {
        return $this->rawJoin($this->throttledNotifications->query()->toBase());
    }

    private function rawJoin(Builder $builder): array
    {
        return [
            'throttled_notifications',
            static function (JoinClause $join) use ($builder): void {
                $join->on('notifications.id', 'throttled_notifications.notification_id')
                    ->mergeWheres($builder->wheres, $builder->bindings);
            },
        ];
    }
}
