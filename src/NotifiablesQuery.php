<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Closure;
use stdClass;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Query\JoinClause;

class NotifiablesQuery
{
    public function each(Closure $closure): void
    {
        $this->query()->each(static function (stdClass $record) use ($closure): void {
            $closure(Notifiable::hydrate($record));
        });
    }

    private function query(): Builder
    {
        return $this->databaseNotifications()
            ->join(...$this->join($this->throttledNotifications()))
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

    private function throttledNotifications(): Builder
    {
        return ThrottledNotification::query()
            ->whereUnsent()
            ->whereNotDelayed()
            ->whereUnreserved()
            ->whereCreatedBefore($this->wait())
            ->toBase();
    }

    private function join(Builder $builder): array
    {
        return [
            'throttled_notifications',
            static function (JoinClause $join) use ($builder): void {
                $join->on('notifications.id', 'throttled_notifications.notification_id')
                    ->mergeWheres($builder->wheres, $builder->bindings);
            },
        ];
    }

    private function wait(): Carbon
    {
        return Carbon::now()->subSeconds(Config::get('throttled-notifications.wait'));
    }
}
