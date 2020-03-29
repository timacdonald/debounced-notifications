<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use TiMacDonald\ThrottledNotifications\Contracts\Delay as DelayContract;

class Delay implements DelayContract
{
    public function until(Model $notifiable): ?Carbon
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
            $date = $notifiable->delayNotificationsUntil();

            \assert($date instanceof Carbon);

            return $date;
        }

        return Carbon::now();
    }
}
