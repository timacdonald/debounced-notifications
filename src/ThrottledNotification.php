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

    public function databaseNotification(): BelongsTo
    {
        return $this->belongsTo(DatabaseNotification::class, 'notification_id');
    }

    public function setPayloadAttribute(ShouldThrottle $notification): void
    {
        $this->attributes['payload'] = serialize($notification);
    }

    public function getPayloadAttribute(string $value): ShouldThrottle
    {
        return unserialize($value);
    }

    public function scopeWhereUnsent(Builder $builder): void
    {
        $builder->whereNull('sent_at');
    }

    public function scopeWhereCreatedBefore(Builder $builder, Carbon $date)
    {
        $builder->where('created_at', '<', $date);
    }

    public function isSent(): bool
    {
        return $this->sent_at !== null;
    }
}
