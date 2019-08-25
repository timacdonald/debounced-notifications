<?php

namespace TiMacDonald\ThrottledNotifications;

interface ShouldThrottle
{
    /**
     * @param mixed $notifiable
     */
    public function throttledVia($notifiable): array;
}
