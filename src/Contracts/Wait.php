<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Contracts;

use Carbon\Carbon;

interface Wait
{
    public function lapsesAt(): Carbon;
}
