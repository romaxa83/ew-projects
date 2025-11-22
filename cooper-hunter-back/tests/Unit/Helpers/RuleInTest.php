<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class RuleInTest extends TestCase
{
    /** @test */
    public function success_some_values(): void
    {
        $str = rule_in('pending', 'done');

        $this->assertEquals($str, 'in:pending,done');
    }

    /** @test */
    public function success_one_value(): void
    {
        $str = rule_in(1);

        $this->assertEquals($str, 'in:1');
    }

    /** @test */
    public function without_value(): void
    {
        $str = rule_in();

        $this->assertNull($str);
    }
}
