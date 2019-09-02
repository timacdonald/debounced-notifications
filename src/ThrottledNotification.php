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

    /**
     * @var array
     */
    protected $casts = [
        'delayed_until' => 'datetime',
    ];

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

    public function isSent(): bool
    {
        return $this->sent_at !== null;
    }

    public function scopeReserve(Builder $builder, string $key): int
    {
        return $builder->update(['reserved_key' => $key]);
    }

    public function scopeWhereReservationKey(Builder $builder, string $key): void
    {
        $builder->where('reserved_key', '=', $key);
    }

    public function scopeWhereNotReserved(Builder $builder): void
    {
        $builder->whereNull('reserved_key');
    }

    public function scopeWhereUnsent(Builder $builder): void
    {
        $builder->whereNull('sent_at');
    }

    public function scopeWhereNotDelayed(Builder $builder): void
    {
        $builder->whereNull('delayed_until');
    }

    public function scopeWhereDelayed(Builder $builder): void
    {
        $builder->whereNotNull('delayed_until');
    }

    public function scopeWhereCreatedBefore(Builder $builder, Carbon $date): void
    {
        $builder->where('created_at', '<', $date);
    }
}
