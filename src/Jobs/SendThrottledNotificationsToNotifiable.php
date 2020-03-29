<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use TiMacDonald\ThrottledNotifications\Contracts\Courier;
use TiMacDonald\ThrottledNotifications\Contracts\Reservables;

class SendThrottledNotificationsToNotifiable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $notifiable;

    /**
     * @var string
     */
    private $key;

    public function __construct(Model $notifiable, string $key)
    {
        $this->notifiable = $notifiable;

        $this->key = $key;
    }

    public function handle(Reservables $reservables, Courier $courier): void
    {
        $count = $reservables->reserve($this->notifiable, $this->key);

        if ($count === 0) {
            return;
        }

        $courier->send($this->notifiable, $reservables->get($this->key));

        $reservables->markAsSent($this->key);
    }

    public function failed(Exception $exception): void
    {
        $reservables = \app(Reservables::class);

        \assert($reservables instanceof Reservables);

        $reservables->release($this->key);
    }

    public function notifiable(): Model
    {
        return $this->notifiable;
    }

    public function key(): string
    {
        return $this->key;
    }
}
