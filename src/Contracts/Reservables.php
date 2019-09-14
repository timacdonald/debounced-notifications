<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Contracts;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

interface Reservables
{
    public function query(Model $notifiable): Builder;

    public function release(string $key): void;

    public function get(string $key): Collection;
}
