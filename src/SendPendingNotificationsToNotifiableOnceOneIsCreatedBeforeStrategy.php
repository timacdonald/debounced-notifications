<?php

namespace TiMacDonald\ThrottledNotifications;

use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

class SendPendingNotificationsToNotifiableOnceOneIsCreatedBeforeStrategy implements ThrottleStrategy
{
    /**
     * @var \Carbon\Carbon
     */
    private $date;

    public function __construct(Carbon $date)
    {
        $this->date = $date;
    }

    public function handle(Closure $closure): void
    {
        DatabaseNotification::query()
            ->whereUnread()
            ->groupByNotifiable()
            ->joinSub($this->throttled(), 'throttled', function (JoinClause $join) {
                $join->on('notifications.id', '=', 'throttled.notification_id');
            })->each($closure);
    }

    private function throttled(): Builder
    {
        return ThrottledNotification::query()
            ->whereUnsent()
            ->whereCreatedBefore($this->date)
            ->select('notification_id');
    }
}
