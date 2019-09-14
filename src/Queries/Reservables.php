<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Queries;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;
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
        $databaseNotifications = $this->databaseNotifications->query()->toBase();

        return $this->throttledNotifications->query()
            ->whereHas('databaseNotification', static function (Builder $builder) use ($notifiable, $databaseNotifications): void {
                $builder->whereNotifiable($notifiable)
                    ->mergeWheres($databaseNotifications->wheres, $databaseNotifications->bindings);
            });
    }

    public function release(string $key): void
    {
        $this->reserved($key)->release();
    }

    public function get(string $key): Collection
    {
        return $this->reserved($key)
            ->oldest()
            ->with(['databaseNotification:type'])
            ->get();
    }

    private function reserved(string $key): Builder
    {
        return ThrottledNotification::query()
            ->whereReservedKey($key);
    }
}
