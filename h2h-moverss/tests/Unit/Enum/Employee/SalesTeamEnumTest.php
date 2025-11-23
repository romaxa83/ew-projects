<?php

namespace Tests\Unit\Enum\Employee;

use App\Enums\Employee\SalesTeamEnum;
use Tests\TestCase;

class SalesTeamEnumTest extends TestCase
{

    /** @test */
    public function check_value()
    {
        $this->assertEquals('local', SalesTeamEnum::Local());
        $this->assertEquals('local_long', SalesTeamEnum::Local_long());
    }

    /** @test */
    public function check_for_select()
    {
        $this->assertEquals(SalesTeamEnum::forSelect('key'), [
            ['key' => SalesTeamEnum::Local->value, 'title' => SalesTeamEnum::Local->labelAsName()],
            ['key' => SalesTeamEnum::Local_long->value, 'title' => SalesTeamEnum::Local_long->labelAsName()],
        ]);
    }

    /** @test */
    public function check_is()
    {
        $local = SalesTeamEnum::Local;
        $long = SalesTeamEnum::Local_long;

        $this->assertTrue($local->isLocal());
        $this->assertFalse($long->isLocal());
    }

    /** @test */
    public function check_rule_in()
    {
        $this->assertEquals(
            SalesTeamEnum::ruleIn(),
            "in:".SalesTeamEnum::Local()
            .",".SalesTeamEnum::Local_long()
        );
    }
}

