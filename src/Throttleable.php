<?php

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Database\Eloquent\Model;

trait Throttleable
{
    public function via(Model $notifiable): array
    {
        return [ThrottleChannel::class];
    }

    public function toArray(Model $notifiable): array
    {
        return [];
    }
}
