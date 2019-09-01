<?php

namespace TiMacDonald\ThrottledNotifications;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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

    protected function setPayloadAttribute(ShouldThrottle $notification): void
    {
        $this->attributes['payload'] = serialize($notification);
    }

    protected function getPayloadAttribute(string $value): ShouldThrottle
    {
        return unserialize($value);
    }

    public function isSent(): bool
    {
        return $this->sent_at !== null;
    }

    public function scopeReserve(Builder $builder, string $key): bool
    {
        return $builder->update([
            'reservation_key' => $key,
        ]);
    }

    public function scopeWhereReservationKey(Builder $builder, string $key): void
    {
        $builder->where('reservation_key', '=', $key);
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
