<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use stdClass;
use Illuminate\Database\Eloquent\Model;

class Notifiable
{
    public const KEY_ATTRIBUTE = 'key';

    public const TYPE_ATTRIBUTE = 'type';

    public static function hydrate(stdClass $record): Model
    {
        return \tap(static::instance($record), static function (Model $instance) use ($record): void {
            $instance->forceFill([$instance->getKeyName() => $record->{static::KEY_ATTRIBUTE}]);
        });
    }

    private static function instance(stdClass $record): Model
    {
        $class = Model::getActualClassNameForMorph($record->{static::TYPE_ATTRIBUTE});

        return $class::newModelInstance();
    }
}
