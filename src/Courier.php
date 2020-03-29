<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Notifications\Dispatcher;
use TiMacDonald\ThrottledNotifications\Contracts\Courier as CourierContract;

class Courier implements CourierContract
{
    /**
     * @var \Illuminate\Contracts\Notifications\Dispatcher
     */
    private $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function send(Model $notifiable, ThrottledNotificationCollection $throttledNotifications): void
    {
        $throttledNotifications
            ->groupByChannel($notifiable)
            // ->map(static function (ThrottledNotificationCollection $throttledNotifications, string $channel): void {
                // return $notification->groupByType();
            ->each(function (Notification $notification) use ($notifiable): void {
                $this->dispatcher->send($notifiable, $notification);
            });
    }
}
