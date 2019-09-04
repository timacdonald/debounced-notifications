<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThrottledNotification extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    public function databaseNotification(): BelongsTo
    {
        return $this->belongsTo(DatabaseNotification::class, 'notification_id');
    }

    protected function setPayloadAttribute(Notification $notification): void
    {
        $this->attributes['payload'] = \serialize($notification);
    }

    protected function getPayloadAttribute(string $value): Notification
    {
        return \unserialize($value);
    }

    public function scopeWhereUnsent(Builder $builder): void
    {
        $builder->whereNull('sent_at');
    }

    public function scopeWhereCreatedBefore(Builder $builder, Carbon $date): void
    {
        $builder->where('throttled_notifications.created_at', '<', $date);
    }

    public function scopeWhereUnreserved(Builder $builder): void
    {
        $builder->whereNull('reserved_key');
    }

    public function scopeWhereNotDelayed(Builder $builder): void
    {
        $builder->whereNull('delayed_until');
    }
}
