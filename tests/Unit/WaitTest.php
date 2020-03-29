<?php

declare(strict_types=1);

namespace Tests\Unit;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use TiMacDonald\ThrottledNotifications\Wait;

class WaitTest extends TestCase
{
    public function testWaitTimeCanBeConfigured(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        $wait = new Wait(54321);
        $expected = Carbon::now()->subSeconds(54321);

        // act
        $date = $wait->lapsesAt();

        // assert
        $this->assertTrue($expected->eq($date));
    }

    public function testCanUseStaticConstructorToSetInMinutes(): void
    {
        // arrange
        Carbon::setTestNow(Carbon::now());
        $expected = new Wait(54321 * 60);

        // act
        $wait = Wait::fromMinutes(54321);

        // assert
        $this->assertTrue($expected->lapsesAt()->eq($wait->lapsesAt()));
    }
}
