<?php

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Channels\DatabaseChannel;

class ThrottleChannel
{
    /**
     * @var \Illuminate\Notifications\Channels\DatabaseChannel
     */
    private $databaseChannel;

    public function __construct(DatabaseChannel $databaseChannel)
    {
        $this->databaseChannel = $databaseChannel;
    }

    /**
     * @param mixed $notifiable
     */
    public function send($notifiable, ShouldThrottle $notification): ThrottledNotification
    {
        return ThrottledNotification::create([
            'payload' => $notification,
            'notification_id' => $this->databaseNotification(...func_get_args())->id,
        ]);
    }

    /**
     * @param mixed $notifiable
     */
    private function databaseNotification($notifiable, ShouldThrottle $notification): DatabaseNotification
    {
        return $this->databaseChannel->send($notifiable, $notification);
    }
}
