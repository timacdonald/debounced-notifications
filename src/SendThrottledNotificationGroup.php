<?php

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendThrottledNotificationGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \TiMacDonald\ThrottledNotifications\DatabaseNotification
     */
    private $databaseNotification;

    public function __construct(DatabaseNotification $databaseNotification)
    {
        $this->databaseNotification = $databaseNotification;
    }

    public function handle(): void
    {
        //
    }

    public function databaseNotification(): DatabaseNotification
    {
        return $this->databaseNotification;
    }
}
