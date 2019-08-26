<?php

namespace TiMacDonald\ThrottledNotifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SendThrottledNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \TiMacDonald\ThrottledNotifications\ThrottleStrategy
     */
    private $throttleStrategy;

    /**
     * @var \TiMacDonald\ThrottledNotifications\SendStrategy
     */
    private $sendStrategy;

    public function __construct(ThrottleStrategy $throttleStrategy, SendStrategy $sendStrategy)
    {
        $this->throttleStrategy = $throttleStrategy;

        $this->sendStrategy = $sendStrategy;
    }

    public function handle(Dispatcher $bus): void
    {
        $this->throttleStrategy->handle($this->sendStrategy->send());
    }
}
