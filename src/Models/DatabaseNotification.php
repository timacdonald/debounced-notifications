<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Models;

use TiMacDonald\ThrottledNotifications\Builders\DatabaseNotificationBuilder;
use Illuminate\Notifications\DatabaseNotification as BaseDatabaseNotification;

/**
 * Attributes.
 *
 * @property string $id
 *
 * Relationships
 * @property \Illuminate\Database\Eloquent\Model $notifiable
 */
class DatabaseNotification extends BaseDatabaseNotification
{
    public static function query(): DatabaseNotificationBuilder
    {
        $query = parent::query();

        \assert($query instanceof DatabaseNotificationBuilder);

        return $query;
    }

    public function newEloquentBuilder($query): DatabaseNotificationBuilder
    {
        return new DatabaseNotificationBuilder($query);
    }
}
