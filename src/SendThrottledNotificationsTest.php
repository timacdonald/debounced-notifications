<?php

namespace TiMacDonald\ThrottledNotifications;

use Carbon\Carbon;
use Closure;
use Illuminate\Database\ConnectionInterface as Database;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Str;

class SendThrottledNotifications
{
    /**
     * @var \Carbon\Carbon
     */
    private $date;

    /**
     * @var string
     */
    private $reservationKey;

    public function __construct(Carbon $date)
    {
        $this->date = $date;

        $this->reservationKey = Str::random(16);
    }

    public function handle(Closure $closure, Database $database): void
    {
        $reserved = $database->transaction(function () {
            return $this->reserveNotifications();
        });

        if ($reserved === 0) {
            return;
        }

        $this->reservedQuery()
            ->

        // do a join with this, select the notification attributes, and then do a group by!
        DatabaseNotification::query()
            ->whereUnread()
            ->groupByNotifiable()
            ->joinSub($this->throttled(), 'throttled', function (JoinClause $join) {
                $join->on('notifications.id', '=', 'throttled.notification_id');
            })->each($closure);
    }

    private function databaseNotifications(): Builder
    {
        return DatabaseNotification::query()
            ->whereUnread();
    }

    private function reservedQuery(): Builder
    {
        return ThrottledNotification::query()
            ->whereReservationKey($this->reservationKey);
    }

    private function reserveNotifications(): int
    {
        return ThrottledNotification::query()
            ->whereUnsent()
            ->whereCreatedBefore($this->date)
            ->reserve($this->reservationKey);
    }
}
