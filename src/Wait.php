<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Carbon\Carbon;
use TiMacDonald\ThrottledNotifications\Contracts\Wait as WaitContract;

class Wait implements WaitContract
{
    /**
     * @var int
     */
    private $seconds;

    public function __construct(int $seconds)
    {
        $this->seconds = $seconds;
    }

    public static function fromMinutes(int $minutes): self
    {
        return new static($minutes * Carbon::SECONDS_PER_MINUTE);
    }

    public function lapsesAt(): Carbon
    {
        return Carbon::now()->subSeconds($this->seconds);
    }
}
