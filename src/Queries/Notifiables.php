<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Queries;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use TiMacDonald\ThrottledNotifications\Contracts\Wait;
use TiMacDonald\ThrottledNotifications\Notifiable;
use TiMacDonald\ThrottledNotifications\Contracts\Notifiables as NotifiablesContract;

class Notifiables implements NotifiablesContract
{
    /**
     * \TiMacDonald\ThrottledNotifications\Queries\ThrottledNotifications.
     */
    private $throttledNotifications;

    /**
     * @var \TiMacDonald\ThrottledNotifications\Queries\DatabaseNotifications
     */
    private $databaseNotifications;

    /**
     * @var \TiMacDonald\ThrottledNotifications\Contracts\Wait
     */
    private $wait;

    public function __construct(ThrottledNotifications $throttledNotifications, DatabaseNotifications $databaseNotifications, Wait $wait)
    {
        $this->throttledNotifications = $throttledNotifications;

        $this->databaseNotifications = $databaseNotifications;

        $this->wait = $wait;
    }

    public function query(): Builder
    {
        $throttledNotifications = $this->throttledNotifications->query()
            ->wherePastWait($this->wait)
            ->toBase();

        return $this->databaseNotifications->query()
            ->orderByOldest()
            ->groupByNotifiable()
            ->toBase()
            ->join('throttled_notifications', static function (JoinClause $join) use ($throttledNotifications): void {
                $join->on('notifications.id', 'throttled_notifications.notification_id')
                    ->mergeWheres($throttledNotifications->wheres, $throttledNotifications->bindings);
            })
            ->select([
                'notifications.notifiable_id as '.Notifiable::KEY_ATTRIBUTE,
                'notifications.notifiable_type as '.Notifiable::TYPE_ATTRIBUTE,
            ]);
    }
}
