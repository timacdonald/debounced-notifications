<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications;

use Illuminate\Notifications\Notification;

class NotificationPayload
{
    /**
     * @var array
     */
    private $store = [];

    /**
     * @var bool
     */
    private $useCache = true;

    /**
     * @var \TiMacDonald\ThrottledNotifications\NotificationPayload|null
     */
    private static $instance;

    public static function getInstance(): self
    {
        return static::$instance ?? static::$instance = new static();
    }

    public function unserialize(string $string): Notification
    {
        if (! $this->useCache) {
            return \unserialize($string);
        }

        return $this->store[$string] ?? $this->store[$string] = \unserialize($string);
    }

    public function dontUseCache(): void
    {
        $this->useCache = false;

        $this->clearCache();
    }

    public function clearCache(): void
    {
        $this->store = [];
    }
}
