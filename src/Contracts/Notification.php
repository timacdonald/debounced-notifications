<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Contracts;

use TiMacDonald\ThrottledNotifications\ThrottledNotificationCollection;

interface Notification
{
    public function send(ThrottledNotificationCollection $notifications): void;
}
