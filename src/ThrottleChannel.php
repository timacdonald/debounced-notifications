<?php

namespace TiMacDonald\ThrottledNotifications;

use Carbon\Carbon;
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
        $delayUntil = $this->delayNotificationsUntil($notifiable);

        return ThrottledNotification::create([
            'payload' => $notification,
            'delayed_until' => ! $delayUntil->isFuture() ? null : $delayUntil,
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

    /**
     * @param mixed $notifiable
     */
    private function delayNotificationsUntil($notifiable): Carbon
    {
        if (method_exists($notifiable, 'delayNotificationsUntil')) {
            return $notifiable->delayNotificationsUntil();
        }

        return Carbon::now();
    }
}
