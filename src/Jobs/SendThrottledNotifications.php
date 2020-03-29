<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Jobs;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use TiMacDonald\ThrottledNotifications\Contracts\Notifiables;

class SendThrottledNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(Dispatcher $bus, Notifiables $notifiables): void
    {
        $notifiables->each(static function (Model $notifiable) use ($bus): void {
            $bus->dispatch(new SendThrottledNotificationsToNotifiable($notifiable, Str::uuid()->toString()));
        });
    }
}
