<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification as BaseNotification;

class Notification extends BaseNotification
{
    /**
     * @var \Illuminate\Notifications\Notification
     */
    private $notification;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $notifiable;

    public function __construct(BaseNotification $notification, Model $notifiable)
    {
        $this->notification = $notification;

        $this->notifiable = $notifiable;
    }

    public function via(Model $notifiable): array
    {
        try {
            return $this->notification->throttledVia($notifiable);
        } catch (BadMethodCallException $exception) {
            // throw custom exception with links to the docs about implementing the throttledVia method.
            // this is an implicit contract to make things easier for the developer.
        }
    }

    public function toBase(): self
    {
        return $this->notification;
    }

    public function __call(string $method, array $arguments)
    {
        return $this->notification->{$method}(...$arguments);
    }

    public function __get(string $property)
    {
        return $this->notification->{$property};
    }

    public function __set(string $property, $value): void
    {
        $this->notification->{$property} = $value;
    }
}
