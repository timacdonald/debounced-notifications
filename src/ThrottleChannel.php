<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
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

    public function send(Model $notifiable, Notification $notification): ThrottledNotification
    {
        return ThrottledNotification::create([
            'payload' => $notification,
            'delayed_until' => Delay::determine($notifiable),
            'notification_id' => $this->databaseChannel->send($notifiable, $notification)->id,
        ]);
    }
}
