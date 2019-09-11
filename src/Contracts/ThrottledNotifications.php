<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface ThrottledNotifications
{
    public function query(): Builder;
}
