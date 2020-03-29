<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Database\Eloquent\Model;

trait Throttleable
{
    public function via(Model $notifiable): array
    {
        return [ThrottleChannel::class];
    }

    public function toArray(): array
    {
        return [];
    }
}
