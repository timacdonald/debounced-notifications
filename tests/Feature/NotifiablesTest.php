<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use TiMacDonald\ThrottledNotifications\Contracts\Notifiables;
use TiMacDonald\ThrottledNotifications\Models\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\Models\ThrottledNotification;

class NotifiablesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::now());
    }

    public function testNotificationsAreIncluded(): void
    {
        // arrange
        \factory(ThrottledNotification::class)->times(2)->create();
        Carbon::setTestNow(Carbon::now()->addMinutes(10));

        // act
        $notifiables = $this->app[Notifiables::class]->query()->get();

        // assert
        $this->assertCount(2, $notifiables);
    }

    public function testOnlyIncludesOnePerNotifiableThatIsTheOldest(): void
    {
        // arrange
        $databaseNotification = \factory(DatabaseNotification::class)->create();
        \factory(ThrottledNotification::class)->create([
            'notification_id' => $databaseNotification->id,
        ]);
        Carbon::setTestNow(Carbon::now()->addMinutes(10));
        $expected = \factory(ThrottledNotification::class)->create([
            'notification_id' => $databaseNotification->id,
        ]);
        Carbon::setTestNow(Carbon::now()->addMinutes(10));

        // act
        $notifiables = $this->app[Notifiables::class]->query()->get();

        // assert
        $this->assertCount(1, $notifiables);
    }

    public function testReadNotificationAreIgnored(): void
    {
        // arrange
        $databaseNotification = \factory(DatabaseNotification::class)->states(['read'])->create();
        \factory(ThrottledNotification::class)->create([
            'notification_id' => $databaseNotification->id,
        ]);
        Carbon::setTestNow(Carbon::now()->addMinutes(10));

        // act
        $notifiables = $this->app[Notifiables::class]->query()->get();

        // assert
        $this->assertCount(0, $notifiables);
    }

    public function testSentNotificationsAreIgnored(): void
    {
        // arrange
        \factory(ThrottledNotification::class)->states(['sent'])->create();
        Carbon::setTestNow(Carbon::now()->addMinutes(10));

        // act
        $notifiables = $this->app[Notifiables::class]->query()->get();

        // assert
        $this->assertCount(0, $notifiables);
    }

    public function testNotificationsBeforeWaitTimeHasLaspedAreIgnored(): void
    {
        // arrange
        \factory(ThrottledNotification::class)->create();
        Carbon::setTestNow(Carbon::now()->addMinutes(10)->subSecond());

        // act
        $notifiables = $this->app[Notifiables::class]->query()->get();

        // assert
        $this->assertCount(0, $notifiables);
    }

    public function testReservedNotificationsAreIgnored(): void
    {
        // arrange
        \factory(ThrottledNotification::class)->states(['reserved'])->create();
        Carbon::setTestNow(Carbon::now()->addMinutes(10));

        // act
        $notifiables = $this->app[Notifiables::class]->query()->get();

        // assert
        $this->assertCount(0, $notifiables);
    }
}
