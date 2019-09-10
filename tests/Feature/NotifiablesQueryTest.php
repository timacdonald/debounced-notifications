<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use TiMacDonald\ThrottledNotifications\NotifiablesQuery;
use TiMacDonald\ThrottledNotifications\DatabaseNotification;
use TiMacDonald\ThrottledNotifications\ThrottledNotification;

class NotifiablesQueryTest extends TestCase
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
        $result = [];
        $this->app[NotifiablesQuery::class]->each(static function (Model $notifiable) use (&$result): void {
            $result[] = $notifiable;
        });

        // assert
        $this->assertCount(2, $result);
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
        $result = [];
        $this->app[NotifiablesQuery::class]->each(static function (Model $notifiable) use (&$result): void {
            $result[] = $notifiable;
        });

        // assert
        $this->assertCount(1, $result);
        $this->assertTrue($expected->databaseNotification->notifiable->is($result[0]));
    }

    public function testDelayedNotificationsAreIgnored(): void
    {
        // arrange
        \factory(ThrottledNotification::class)->states(['delayed'])->create();
        Carbon::setTestNow(Carbon::now()->addMinutes(10));

        // act
        $result = [];
        $this->app[NotifiablesQuery::class]->each(static function (Model $notifiable) use (&$result): void {
            $result[] = $notifiable;
        });

        // assert
        $this->assertCount(0, $result);
    }

    public function testSentNotificationsAreIgnored(): void
    {
        // arrange
        \factory(ThrottledNotification::class)->states(['sent'])->create();
        Carbon::setTestNow(Carbon::now()->addMinutes(10));

        // act
        $result = [];
        $this->app[NotifiablesQuery::class]->each(static function (Model $notifiable) use (&$result): void {
            $result[] = $notifiable;
        });

        // assert
        $this->assertCount(0, $result);
    }

    public function testNotificationsBeforeWaitTimeHasLaspedAreIgnored(): void
    {
        // arrange
        \factory(ThrottledNotification::class)->create();
        Carbon::setTestNow(Carbon::now()->addMinutes(10)->subSecond());

        // act
        $result = [];
        $this->app[NotifiablesQuery::class]->each(static function (Model $notifiable) use (&$result): void {
            $result[] = $notifiable;
        });

        // assert
        $this->assertCount(0, $result);
    }

    public function testReservedNotificationsAreIgnored(): void
    {
        // arrange
        \factory(ThrottledNotification::class)->states(['reserved'])->create();
        Carbon::setTestNow(Carbon::now()->addMinutes(10));

        // act
        $result = [];
        $this->app[NotifiablesQuery::class]->each(static function (Model $notifiable) use (&$result): void {
            $result[] = $notifiable;
        });

        // assert
        $this->assertCount(0, $result);
    }
}
