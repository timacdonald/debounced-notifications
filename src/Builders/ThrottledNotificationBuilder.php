<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Builders;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use TiMacDonald\ThrottledNotifications\Contracts\Wait;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class ThrottledNotificationBuilder extends EloquentBuilder
{
    public function whereUnsent(): self
    {
        $this->query->whereNull('sent_at');

        return $this;
    }

    public function wherePastWait(Wait $wait): self
    {
        $this->where('throttled_notifications.created_at', '<=', $wait->lapsesAt());

        return $this;
    }

    public function whereUnreserved(): self
    {
        $this->whereNull('reserved_key');

        return $this;
    }

    public function whereNotDelayed(): self
    {
        $this->whereNull('delayed_until');

        return $this;
    }

    public function whereReservedKey(string $key): self
    {
        $this->where('reserved_key', '=', $key);

        return $this;
    }

    public function whereHasDatabaseNotifications(QueryBuilder $databaseNotifications): self
    {
        $this->whereHas('databaseNotification', static function (EloquentBuilder $builder) use ($databaseNotifications): void {
            $builder->mergeWheres($databaseNotifications->wheres, $databaseNotifications->bindings);
        });

        return $this;
    }

    public function reserve(string $key): int
    {
        return $this->update(['reserved_key' => $key]);
    }

    public function release(): int
    {
        return $this->update(['reserved_key' => null]);
    }

    public function markAsSent(): int
    {
        return $this->update(['sent_at' => Carbon::now()]);
    }
}
