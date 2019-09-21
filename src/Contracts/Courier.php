<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Contracts;

use Illuminate\Database\Eloquent\Model;
use TiMacDonald\ThrottledNotifications\ThrottledNotificationCollection;

interface Courier
{
    public function send(Model $notifiable, ThrottledNotificationCollection $notifications): void;
}
