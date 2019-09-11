<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\ConnectionInterface as Database;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;
use TiMacDonald\ThrottledNotifications\Contracts\ThrottledNotifications;

class SendThrottledNotificationsToNotifiable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $notifiable;

    /**
     * @var string
     */
    private $reservationKey;

    public function __construct(Model $notifiable, string $reservationKey)
    {
        $this->notifiable = $notifiable;

        $this->reservationKey = $reservationKey;
    }

    public function handle(Database $database, ThrottledNotifications $throttledNotifications): void
    {
        $count = $database->transaction(function () use ($throttledNotifications): int {
            return $this->reserve($throttledNotifications);
        });

        if ($count === 0) {
            return;
        }

        $this->reserved()
            ->groupBy('databaseNotification.type');
        //
    }

    private function reserve(ThrottledNotifications $throttledNotifications): int
    {
        return $throttledNotifications->query()
            ->whereHas('notifiable', function (Builder $builder): void {
                $builder->whereNotifiable($this->notifiable)
                    ->whereUnread();
            })
            ->reserve($this->reservationKey);
    }

    private function reserved(): Collection
    {
        return ThrottledNotification::query()
            ->whereReservedKey($this->reservationKey)
            ->oldest()
            ->with(['databaseNotification:type'])
            ->get();
    }
}
