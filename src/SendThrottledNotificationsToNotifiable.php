<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendThrottledNotificationsToNotifiable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $notifiable;

    public function __construct(Model $notifiable)
    {
        $this->notifiable = $notifiable;
    }

    public function notifiable(): Model
    {
        return $this->notifiable;
    }
}
