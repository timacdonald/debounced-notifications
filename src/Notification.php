<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use TiMacDonald\ThrottledNotifications\Contracts\Notification as NotificationContract;

class Notification implements NotificationContract
{
    public function send(ThrottledNotificationCollection $throttledNotifications): void
    {
        //
    }
}
