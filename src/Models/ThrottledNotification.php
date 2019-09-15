<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use TiMacDonald\ThrottledNotifications\Contracts\Wait;
use TiMacDonald\ThrottledNotifications\NotificationPayload;

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
        return NotificationPayload::getInstance()->unserialize($value);
    }

    public function scopeWhereUnsent(Builder $builder): void
    {
        $builder->whereNull('sent_at');
    }

    public function scopeWherePastWait(Builder $builder, Wait $wait): void
    {
        $builder->where('throttled_notifications.created_at', '<=', $wait->lapsesAt());
    }

    public function scopeWhereUnreserved(Builder $builder): void
    {
        $builder->whereNull('reserved_key');
    }

    public function scopeWhereReservationKey(Builder $builder, string $key): void
    {
        $builder->where('reserved_key', '=', $key);
    }

    public function scopeWhereNotDelayed(Builder $builder): void
    {
        $builder->whereNull('delayed_until');
    }

    public function scopeReserve(Builder $builder, string $key): int
    {
        return $builder->update(['reserved_key' => $key]);
    }

    public function scopeRelease(Builder $builder): int
    {
        return $builder->update(['reserved_key' => null]);
    }

    public function scopeWhereHasDatabaseNotifications(Builder $builder, QueryBuilder $databaseNotifications): void
    {
        $builder->whereHas('databaseNotification', static function (Builder $builder) use ($databaseNotifications): void {
            $builder->mergeWheres($databaseNotifications->wheres, $databaseNotifications->bindings);
        });
    }
}
