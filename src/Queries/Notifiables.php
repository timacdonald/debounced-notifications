<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Queries;

use stdClass;
use TiMacDonald\ThrottledNotifications\Notifiable;
use TiMacDonald\ThrottledNotifications\Contracts\Wait;
use TiMacDonald\ThrottledNotifications\Builders\DatabaseNotificationBuilder;
use TiMacDonald\ThrottledNotifications\Builders\ThrottledNotificationBuilder;
use TiMacDonald\ThrottledNotifications\Contracts\Notifiables as NotifiablesContract;

class Notifiables implements NotifiablesContract
{
    /**
     * @var \TiMacDonald\ThrottledNotifications\Queries\ThrottledNotifications
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

    public function each(callable $callback): void
    {
        $this->query()
            ->toBase()
            ->each(static function (stdClass $record) use ($callback): void {
                $callback(Notifiable::hydrate($record));
            });
    }

    private function query(): DatabaseNotificationBuilder
    {
        return $this->databaseNotifications->query()
            ->orderByOldest()
            ->groupByNotifiable()
            ->selectNotifiable()
            ->joinThrottledNotifications($this->throttledNotifications());
    }

    private function throttledNotifications(): ThrottledNotificationBuilder
    {
        return $this->throttledNotifications->query()
            ->wherePastWait($this->wait);
    }
}
