<?php

namespace TiMacDonald\ThrottledNotifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SendThrottledNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \Carbon\Carbon
     */
    private $carbon;

    public function __construct(Carbon $before)
    {
        $this->before = $before;
    }

    public function handle(Dispatcher $bus): void
    {
        DatabaseNotification::query()
            ->whereUnread()
            ->joinSub($this->throttled(), 'throttled', function ($join) {
                $join->on('notifications.id', '=', 'throttled.notification_id');
            })
            ->groupBy('notifiable_type', 'notifiable_id')
            ->each(function (DatabaseNotification $notification) use ($bus) {
                $bus->dispatch(new SendThrottledNotificationGroup($notification));
            });
    }

    private function throttled(): Builder
    {
        return ThrottledNotification::query()
            ->whereUnsent()
            ->whereCreatedBefore($this->before)
            ->select('notification_id');
    }
}
