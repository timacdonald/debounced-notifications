<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Queries;

use Illuminate\Database\Eloquent\Model;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;
use TiMacDonald\ThrottledNotifications\ThrottledNotificationCollection;
use TiMacDonald\ThrottledNotifications\Builders\DatabaseNotificationBuilder;
use TiMacDonald\ThrottledNotifications\Builders\ThrottledNotificationBuilder;
use TiMacDonald\ThrottledNotifications\Contracts\Reservables as ReservablesContract;

class Reservables implements ReservablesContract
{
    /**
     * @var \TiMacDonald\ThrottledNotifications\Queries\ThrottledNotifications
     */
    private $throttledNotifications;

    /**
     * @var \TiMacDonald\ThrottledNotifications\Queries\DatabaseNotifications
     */
    private $databaseNotifications;

    public function __construct(DatabaseNotifications $databaseNotifications, ThrottledNotifications $throttledNotifications)
    {
        $this->databaseNotifications = $databaseNotifications;

        $this->throttledNotifications = $throttledNotifications;
    }

    public function reserve(Model $notifiable, string $key): int
    {
        return $this->throttledNotifications->query()
            ->whereHasDatabaseNotifications($this->databaseNotifications($notifiable)->toBase())
            ->reserve($key);
    }

    public function get(string $key): ThrottledNotificationCollection
    {
        return new ThrottledNotificationCollection($this->reservedThrottledNotifications($key)->get());
    }

    public function release(string $key): int
    {
        return $this->reservedThrottledNotifications($key)->release();
    }

    public function markAsSent(string $key): int
    {
        return $this->reservedThrottledNotifications($key)->markAsSent();
    }

    private function reservedThrottledNotifications(string $key): ThrottledNotificationBuilder
    {
        return ThrottledNotification::query()
            ->whereReservedKey($key)
            ->oldest();
    }

    private function databaseNotifications(Model $notifiable): DatabaseNotificationBuilder
    {
        return $this->databaseNotifications->query()
            ->whereNotifiable($notifiable);
    }
}
