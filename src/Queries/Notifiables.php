<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Queries;

use Illuminate\Database\Eloquent\Builder;
use TiMacDonald\ThrottledNotifications\Contracts\Wait;
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
        return $this->databaseNotifications->query()
            ->orderByOldest()
            ->groupByNotifiable()
            ->selectNotifiable()
            ->joinThrottledNotifications($this->throttledNotifications()->toBase());
    }

    private function throttledNotifications(): Builder
    {
        return $this->throttledNotifications->query()
            ->wherePastWait($this->wait);
    }
}
