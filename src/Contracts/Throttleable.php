<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Throttleable
{
    public function throttledVia(Model $notifiable): array;
}
