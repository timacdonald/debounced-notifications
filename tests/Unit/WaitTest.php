<?php

declare(strict_types=1);

namespace Tests\Unit;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use TiMacDonald\ThrottledNotifications\Wait;

class WaitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::now());
    }

    public function testWaitTimeCanBeConfigured(): void
    {
        // arrange
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
        $expected = new Wait(54321 * 60);

        // act
        $wait = Wait::fromMinutes(54321);

        // assert
        $this->assertTrue($expected->lapsesAt()->eq($wait->lapsesAt()));
    }
}
