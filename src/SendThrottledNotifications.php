<?php

namespace TiMacDonald\ThrottledNotifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SendThrottledNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(Dispatcher $bus): void
    {
        $this->query()->each(function (DatabaseNotification $databaseNotification) {
            dd(func_get_args());
        });
    }

    private function query()
    {
        return $this->databaseNotifications()
            ->leftJoinSub($this->throttledNotifications(), 'throttled_notifications', function (JoinClause $join) {
                $join->on('notifications.id', '=', 'throttled_notifications.notification_id');
            })
            ->groupBy(['notifiable_type', 'notifiable_id']);
    }

    private function throttledNotifications()
    {
        return ThrottledNotification::query()
            ->whereUnsent()
            ->whereNotDelayed()
            ->whereCreatedBefore(now()->subSeconds(config('throttled-notifications.wait')));
    }

    private function databaseNotifications()
    {
        return DatabaseNotification::query()
            ->whereUnread()
            ->oldest();
    }
}
