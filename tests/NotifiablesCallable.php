<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Eloquent\Model;

class NotifiablesCallable
{
    /**
     * @var \Illuminate\Database\Eloquent\Model[]
     */
    public $received = [];

    public function __invoke(Model $model): void
    {
        $this->received[] = $model;
    }
}
