<?php

namespace TiMacDonald\ThrottledNotifications;

trait Throttleable
{
    /**
     * @param mixed $notifiable
     */
    public function via($notifiable): array
    {
        return [ThrottleChannel::class];
    }

    /**
     * @param mixed $notifiable
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}
