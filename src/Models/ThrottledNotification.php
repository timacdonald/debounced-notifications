<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TiMacDonald\ThrottledNotifications\Contracts\Throttleable;
use TiMacDonald\ThrottledNotifications\Builders\ThrottledNotificationBuilder;

/**
 * Attributes.
 *
 * @property string $id
 * @property \Carbon\Carbon|null $delayed_until
 * @property \Carbon\Carbon|null $sent_at
 * @property \TiMacDonald\ThrottledNotifications\Contracts\Throttleable $payload
 * @property string|null $reserved_key
 *
 * Relationships
 * @property \TiMacDonald\ThrottledNotifications\Models\DatabaseNotification $databaseNotification
 */
class ThrottledNotification extends Model
{
    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'delayed_until' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(static function (self $instance): void {
            $instance->id = Str::uuid()->toString();
        });
    }

    public function databaseNotification(): BelongsTo
    {
        return $this->belongsTo(DatabaseNotification::class, 'notification_id');
    }

    protected function setPayloadAttribute(Throttleable $notification): void
    {
        $this->attributes['payload'] = \serialize($notification);
    }

    protected function getPayloadAttribute(string $value): Throttleable
    {
        $notification = \unserialize($value);

        \assert($notification instanceof Throttleable);

        return $notification;
    }

    public static function query(): ThrottledNotificationBuilder
    {
        $query = parent::query();

        \assert($query instanceof ThrottledNotificationBuilder);

        return $query;
    }

    public function newEloquentBuilder($query): ThrottledNotificationBuilder
    {
        return new ThrottledNotificationBuilder($query);
    }
}
