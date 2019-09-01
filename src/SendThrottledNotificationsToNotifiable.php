<?php

namespace TiMacDonald\ThrottledNotifications;

class SendThrottledNotificationsToNotifiable
{
    /**
     * @var mixed
     */
    private $notifiable;

    /**
     * @param mixed $notifiable
     */
    public function __construct($notifiable)
    {
        $this->notifiable = $notifiable;
    }

    /**
     * @return mixed
     */
    public function notifiable()
    {
        return $this->notifiable;
    }
}
