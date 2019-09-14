<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use stdClass;
use Illuminate\Database\Eloquent\Model;

class Notifiable
{
    const KEY_ATTRIBUTE = 'key';

    const TYPE_ATTRIBUTE = 'type';

    public static function hydrate(stdClass $record): Model
    {
        return \tap(static::instance($record), static function (Model $instance) use ($record): void {
            $instance->forceFill([$instance->getKeyName() => $record->key]);
        });
    }

    private static function instance(stdClass $record): Model
    {
        $class = Model::getActualClassNameForMorph($record->type);

        return $class::newModelInstance();
    }
}
