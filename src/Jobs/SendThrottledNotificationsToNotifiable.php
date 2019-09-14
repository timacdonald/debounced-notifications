<?php

declare(strict_types=1);

namespace TiMacDonald\ThrottledNotifications\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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

    public function handle(Reservables $reservables): void
    {
        $count = $reservables->query($this->notifiable)->reserve($this->key);

        if ($count === 0) {
            return;
        }

        $reservables->get($this->key);
    }

    public function failed(Exception $exception): void
    {
        app(Reservables::class)->release($this->key);
    }
}
