<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Channels\DatabaseChannel;
use TiMacDonald\ThrottledNotifications\Contracts\Delay;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;

class ThrottleChannel
{
    /**
     * @var \Illuminate\Notifications\Channels\DatabaseChannel
     */
    private $databaseChannel;

    /**
     * @var \TiMacDonald\ThrottledNotifications\Contracts\Delay
     */
    private $delay;

    public function __construct(DatabaseChannel $databaseChannel, Delay $delay)
    {
        $this->databaseChannel = $databaseChannel;

        $this->delay = $delay;
    }

    public function send(Model $notifiable, Notification $notification): ThrottledNotification
    {
        $notification = ThrottledNotification::query()->create([
            'payload' => $notification,
            'delayed_until' => $this->delay->until($notifiable),
            'notification_id' => $this->databaseChannel->send($notifiable, $notification)->getKey(),
        ]);

        \assert($notification instanceof ThrottledNotification);

        return $notification;
    }
}
