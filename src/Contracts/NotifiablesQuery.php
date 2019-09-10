<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Contracts;

use Closure;

interface NotifiablesQuery
{
    public function each(Closure $closure): void;
}
