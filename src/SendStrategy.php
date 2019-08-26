<?php

namespace TiMacDonald\ThrottledNotifications;

use Closure;

interface SendStrategy
{
    public function send(): Closure;
}
