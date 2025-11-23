<?php

namespace Tests\Unit\Enum\Order;

use App\Enums\Orders\MoveTypeEnum;
use Tests\TestCase;

class MoveTypeEnumTest extends TestCase
{

    /** @test */
    public function check_value()
    {
        $this->assertEquals('local', MoveTypeEnum::Local());
        $this->assertEquals('interstate', MoveTypeEnum::Interstate());
    }

    /** @test */
    public function check_for_select()
    {
        $this->assertEquals(MoveTypeEnum::forSelect('key'), [
            ['key' => MoveTypeEnum::Local->value, 'title' => MoveTypeEnum::Local->labelAsName()],
            ['key' => MoveTypeEnum::Interstate->value, 'title' => MoveTypeEnum::Interstate->labelAsName()],
        ]);
    }

    /** @test */
    public function check_is()
    {
        $local = MoveTypeEnum::Local;
        $interstate = MoveTypeEnum::Interstate;

        $this->assertTrue($local->isLocal());
        $this->assertFalse($interstate->isLocal());
    }

    /** @test */
    public function check_rule_in()
    {
        $this->assertEquals(
            MoveTypeEnum::ruleIn(),
            "in:".MoveTypeEnum::Local()
            .",".MoveTypeEnum::Interstate()
        );
    }
}

