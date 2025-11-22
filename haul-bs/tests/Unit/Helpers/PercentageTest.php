<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class PercentageTest extends TestCase
{
    /** @test */
    public function get_percent(): void
    {
        $number = 200;
        $percent = 10;

        $this->assertEquals(20, percentage($number, $percent));
    }
}
