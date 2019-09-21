<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Database\Eloquent\Collection;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;

class ThrottledNotificationCollection extends Collection
{
    public function groupedByNotificationType(): self
    {
        return $this->groupedBy(static function (ThrottledNotification $notification) {
            return \get_class($notification->payload);
        });
    }
}
