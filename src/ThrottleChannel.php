<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
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

    public function send(Model $notifiable, Notification $notification): ThrottledNotification
    {
        $delayUntil = $this->delayNotificationsUntil($notifiable);

        return ThrottledNotification::create([
            'payload' => $notification,
            'delayed_until' => ! $delayUntil->isFuture() ? null : $delayUntil,
            'notification_id' => $this->databaseNotification($notifiable, $notification)->id,
        ]);
    }

    private function databaseNotification(Model $notifiable, Notification $notification): Model
    {
        return $this->databaseChannel->send($notifiable, $notification);
    }

    private function delayNotificationsUntil(Model $notifiable): Carbon
    {
        if (\method_exists($notifiable, 'delayNotificationsUntil')) {
            return $notifiable->delayNotificationsUntil();
        }

        return Carbon::now();
    }
}
