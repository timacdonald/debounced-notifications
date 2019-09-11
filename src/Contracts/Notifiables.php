<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Contracts;

use Illuminate\Database\Query\Builder;

interface Notifiables
{
    public function query(): Builder;
}
