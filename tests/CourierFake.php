<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Assert;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use TiMacDonald\ThrottledNotifications\Contracts\Courier;
use TiMacDonald\ThrottledNotifications\ThrottledNotificationCollection;

class CourierFake implements Courier
{
    /**
     * @array
     */
    private $sent = [];

    public function send(Model $notifiable, ThrottledNotificationCollection $notifications): void
    {
        $this->sent[] = [$notifiable, $notifications];
    }

    public function assertNothingSent(): void
    {
        Assert::assertCount(0, $this->sent);
    }

    public function assertSent(callable $callback): void
    {
        Assert::assertTrue($this->sent($callback)->isNotEmpty());
    }

    private function sent(callable $callback): Collection
    {
        return Collection::make($this->sent)->filter(static function (array $tuple) use ($callback): bool {
            list($model, $notifications) = $tuple;

            return $callback($model, $notifications);
        });
    }
}
