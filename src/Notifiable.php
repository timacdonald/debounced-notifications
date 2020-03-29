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
        \assert(\property_exists($record, self::TYPE_ATTRIBUTE));

        \assert(\property_exists($record, self::KEY_ATTRIBUTE));

        $type = $record->{self::TYPE_ATTRIBUTE};

        \assert(\is_string($type));

        $class = Model::getActualClassNameForMorph($type);

        $instance = $class::newModelInstance();

        \assert($instance instanceof Model);

        return $instance->forceFill([$instance->getKeyName() => $record->{static::KEY_ATTRIBUTE}]);
    }
}
