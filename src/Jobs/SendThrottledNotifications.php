<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Jobs;

use stdClass;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use TiMacDonald\ThrottledNotifications\Notifiable;
use TiMacDonald\ThrottledNotifications\Contracts\Notifiables;

class SendThrottledNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(Dispatcher $bus, Notifiables $notifiables): void
    {
        $notifiables->query()->toBase()->each(static function (stdClass $notifiable) use ($bus): void {
            $bus->dispatch(new SendThrottledNotificationsToNotifiable(Notifiable::hydrate($notifiable), Str::random(16)));
        });
    }
}
