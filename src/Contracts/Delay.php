<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

interface Delay
{
    public function until(Model $notifiable): ?Carbon;
}
