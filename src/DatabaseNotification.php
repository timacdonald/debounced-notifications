<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\DatabaseNotification as BaseDatabaseNotification;

class DatabaseNotification extends BaseDatabaseNotification
{
    public function throttledNotification(): HasOne
    {
        return $this->hasOne(ThrottledNotification::class, 'notification_id');
    }

    public function scopeWhereUnread(Builder $builder): void
    {
        $builder->whereNull('read_at');
    }

    public function scopeWhereCreatedBefore(Builder $builder, Carbon $date): void
    {
        $builder->where('created_at', '<', $date);
    }
}
