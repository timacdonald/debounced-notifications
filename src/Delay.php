<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Carbon\Carbon;

class Delay
{
    public static function determine(object $object): ?Carbon
    {
        $date = static::asDate($object);

        if ($date->isFuture()) {
            return $date;
        }

        return null;
    }

    private static function asDate(object $object): Carbon
    {
        if (\method_exists($object, 'delayNotificationsUntil')) {
            return $object->delayNotificationsUntil() ?? Carbon::now();
        }

        return Carbon::now();
    }
}
