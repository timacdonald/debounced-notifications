<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Queries;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;
use TiMacDonald\ThrottledNotifications\ThrottledNotificationCollection;
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

    public function query(Model $notifiable): Builder
    {
        return $this->throttledNotifications->query()
            ->whereHasDatabaseNotifications($this->databaseNotifications($notifiable)->toBase());
    }

    public function get(string $key): ThrottledNotificationCollection
    {
        return new ThrottledNotificationCollection($this->reservedThrottledNotifications($key)->get());
    }

    public function release(string $key): void
    {
        $this->reservedThrottledNotifications($key)->release();
    }

    public function markAsSent(string $key): void
    {
        $this->reservedThrottledNotifications($key)->markAsSent();
    }

    private function reservedThrottledNotifications(string $key): Builder
    {
        return ThrottledNotification::query()
            ->whereReservedKey($key)
            ->oldest();
    }

    private function databaseNotifications(Model $notifiable): Builder
    {
        return $this->databaseNotifications->query()
            ->whereNotifiable($notifiable);
    }
}
