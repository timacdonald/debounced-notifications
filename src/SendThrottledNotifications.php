<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use stdClass;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Query\Builder;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendThrottledNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(Dispatcher $bus): void
    {
        $this->notifiables()->each(function (stdClass $notifiable) use ($bus): void {
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
            static function (JoinClause $join): void {
                $join->on('notifications.id', '=', 'throttled_notifications.notification_id');
            },
        ];
    }

    private function hydrate(stdClass $notifiable): Model
    {
        return tap($notifiable->type::newModelInstance(), static function (Model $instance) use ($notifiable): void {
            $instance->forceFill([$instance->getKeyName() => $notifiable->id]);
        });
    }
}
