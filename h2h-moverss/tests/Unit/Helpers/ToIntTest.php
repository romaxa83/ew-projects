<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class ToIntTest extends TestCase
{

    /** @test */
    public function success()
    {
        $this->assertEquals(22, to_int(22.3));
        $this->assertEquals(22, to_int('22.3'));
        $this->assertEquals(23, to_int(22.7));
        $this->assertEquals(23, to_int('22.7'));
        $this->assertEquals(22, to_int(22.00));
        $this->assertEquals(22, to_int(22));
        $this->assertEquals(22, to_int('22'));
        $this->assertEquals(0, to_int(0));
        $this->assertEquals(0, to_int('0'));
    }
}