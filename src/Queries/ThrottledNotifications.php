<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Queries;

use Illuminate\Database\Eloquent\Builder;
use TiMacDonald\ThrottledNotifications\Contracts\Wait;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;
use TiMacDonald\ThrottledNotifications\Contracts\ThrottledNotifications as ThrottledNotificationsContract;

class ThrottledNotifications implements ThrottledNotificationsContract
{
    /**
     * @var \TiMacDonald\ThrottledNotifications\Contracts\Wait
     */
    private $wait;

    public function __construct(Wait $wait)
    {
        $this->wait = $wait;
    }

    public function query(): Builder
    {
        return ThrottledNotification::query()
            ->oldest()
            ->whereUnsent()
            ->whereNotDelayed()
            ->whereUnreserved()
            ->wherePastWait($this->wait);
    }
}
