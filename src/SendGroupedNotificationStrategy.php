<?php

namespace TiMacDonald\ThrottledNotifications;

use Closure;
use Illuminate\Contracts\Bus\Dispatcher;

class SendGroupedNotificationStrategy implements SendStrategy
{
    /**
     * @var \Illuminate\Contracts\Bus\Dispatcher
     */
    private $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function send(): Closure
    {
        return function (DatabaseNotification $databaseNotification) {
            $this->dispatcher->dispatch(new SendThrottledNotificationGroup($databaseNotification));
        };
    }
}
