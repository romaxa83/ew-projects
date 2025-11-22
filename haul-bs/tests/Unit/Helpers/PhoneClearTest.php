<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class PhoneClearTest extends TestCase
{
    /** @test */
    public function phone_clear(): void
    {
        $phone = '+38(095) 450-00-11';

        $this->assertEquals('380954500011', phone_clear($phone));
    }
}
