<?php

namespace TiMacDonald\ThrottledNotifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class SendThrottledNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(Dispatcher $bus): void
    {
        $this->notifiables()->each(function (stdClass $notifiable) use ($bus) {
            $bus->dispatch(new SendThrottledNotificationsToNotifiable($this->hydrate($notifiable)));
        });
    }

    private function notifiables(): Builder
    {
        return $this->databaseNotifications()
            ->joinSub(...$this->join())
            ->groupBy([
                'notifiable_id',
                'notifiable_type',
            ])
            ->select([
                'notifications.notifiable_id as id',
                'notifications.notifiable_type as type',
            ])
            ->oldest('notifications.created_at');
    }

    private function throttledNotifications(): Builder
    {
        return ThrottledNotification::query()
            ->whereUnsent()
            ->whereNotDelayed()
            ->whereNotReserved()
            ->whereCreatedBefore($this->before())
            ->getQuery();
    }

    private function databaseNotifications(): Builder
    {
        return DatabaseNotification::query()
            ->whereUnread()
            ->getQuery();
    }

    private function before(): Carbon
    {
        return Carbon::now()->subSeconds($this->wait());
    }

    private function wait(): int
    {
        return config('throttled-notifications.wait');
    }

    private function join(): array
    {
        return [
            $this->throttledNotifications(),
            'throttled_notifications',
            function (JoinClause $join) {
                $join->on('notifications.id', '=', 'throttled_notifications.notification_id');
            },
        ];
    }

    private function hydrate(stdClass $notifiable): Model
    {
        return tap($notifiable->type::newModelInstance(), function (Model $instance) use ($notifiable) {
            $instance->forceFill([$instance->getKeyName() => $notifiable->id]);
        });
    }
}
