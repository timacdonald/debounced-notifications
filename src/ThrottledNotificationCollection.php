<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;

class ThrottledNotificationCollection extends Collection
{
    public function groupByChannel(Model $notifiable): self
    {
        return $this->groupBy(static function (ThrottledNotification $notification) use ($notifiable): array {
            return $notification->payload->throttledVia($notifiable);
        });
    }

    public function groupByNotificationType(): self
    {
        return $this->groupBy(static function (ThrottledNotification $notification): string {
            return \get_class($notification->payload);
        });
    }
}
