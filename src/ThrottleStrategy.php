<?php

namespace TiMacDonald\ThrottledNotifications;

use Closure;

interface ThrottleStrategy
{
    public function handle(Closure $closure): void;
}
