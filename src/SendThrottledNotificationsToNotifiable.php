<?php

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Database\Eloquent\Model;

class SendThrottledNotificationsToNotifiable
{
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
