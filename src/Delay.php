<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Delay
{
    public static function until(Model $notifiable): ?Carbon
    {
        $date = static::asDate($notifiable);

        if ($date->isFuture()) {
            return $date;
        }

        return null;
    }

    private static function asDate(Model $notifiable): Carbon
    {
        if (\method_exists($notifiable, 'delayNotificationsUntil')) {
            return $notifiable->delayNotificationsUntil();
        }

        return Carbon::now();
    }
}
