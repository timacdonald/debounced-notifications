<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Contracts;

interface Notifiables
{
    public function each(callable $callback): void;
}
