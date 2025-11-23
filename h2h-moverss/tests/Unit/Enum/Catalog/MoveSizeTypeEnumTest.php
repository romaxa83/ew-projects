<?php

namespace Tests\Unit\Enum\Catalog;

use App\Enums\Catalog\MoveSizeTypeEnum;
use Tests\TestCase;

class MoveSizeTypeEnumTest extends TestCase
{

    /** @test */
    public function check_value()
    {
        $this->assertEquals('1', MoveSizeTypeEnum::Studio());
        $this->assertEquals('2', MoveSizeTypeEnum::Bedroom_1());
        $this->assertEquals('3', MoveSizeTypeEnum::Bedroom_2());
        $this->assertEquals('4', MoveSizeTypeEnum::Bedroom_3());
        $this->assertEquals('5', MoveSizeTypeEnum::Bedroom_4());
        $this->assertEquals('6', MoveSizeTypeEnum::Storage());
    }

    /** @test */
    public function check_label()
    {
        $this->assertEquals('Studio', MoveSizeTypeEnum::Studio->labelAsName());
        $this->assertEquals('1 Bedroom', MoveSizeTypeEnum::Bedroom_1->labelAsName());
        $this->assertEquals('2 Bedroom', MoveSizeTypeEnum::Bedroom_2->labelAsName());
        $this->assertEquals('3 Bedroom', MoveSizeTypeEnum::Bedroom_3->labelAsName());
        $this->assertEquals('4 Bedroom +', MoveSizeTypeEnum::Bedroom_4->labelAsName());
        $this->assertEquals('Storage', MoveSizeTypeEnum::Storage->labelAsName());
    }

    /** @test */
    public function check_get_label_by_name()
    {
        $this->assertEquals('Studio', MoveSizeTypeEnum::getLabelAsNameByValue(MoveSizeTypeEnum::Studio->value));
        $this->assertEquals('1 Bedroom', MoveSizeTypeEnum::getLabelAsNameByValue(MoveSizeTypeEnum::Bedroom_1->value));
        $this->assertEquals('2 Bedroom', MoveSizeTypeEnum::getLabelAsNameByValue(MoveSizeTypeEnum::Bedroom_2->value));
        $this->assertEquals('3 Bedroom', MoveSizeTypeEnum::getLabelAsNameByValue(MoveSizeTypeEnum::Bedroom_3->value));
        $this->assertEquals('4 Bedroom +', MoveSizeTypeEnum::getLabelAsNameByValue(MoveSizeTypeEnum::Bedroom_4->value));
        $this->assertEquals('Storage', MoveSizeTypeEnum::getLabelAsNameByValue(MoveSizeTypeEnum::Storage->value));

        $this->assertNull(MoveSizeTypeEnum::getLabelAsNameByValue(null));
    }

    /** @test */
    public function check_for_select()
    {
        $this->assertEquals(MoveSizeTypeEnum::forSelect(), [
            ['id' => MoveSizeTypeEnum::Studio->value, 'title' => MoveSizeTypeEnum::Studio->labelAsName()],
            ['id' => MoveSizeTypeEnum::Bedroom_1->value, 'title' => MoveSizeTypeEnum::Bedroom_1->labelAsName()],
            ['id' => MoveSizeTypeEnum::Bedroom_2->value, 'title' => MoveSizeTypeEnum::Bedroom_2->labelAsName()],
            ['id' => MoveSizeTypeEnum::Bedroom_3->value, 'title' => MoveSizeTypeEnum::Bedroom_3->labelAsName()],
            ['id' => MoveSizeTypeEnum::Bedroom_4->value, 'title' => MoveSizeTypeEnum::Bedroom_4->labelAsName()],
            ['id' => MoveSizeTypeEnum::Storage->value, 'title' => MoveSizeTypeEnum::Storage->labelAsName()],
        ]);
    }

    /** @test */
    public function wrong_value_exception()
    {
        $value = '53';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Enum not found by value: ' . $value);

        MoveSizeTypeEnum::getLabelAsNameByValue($value);
    }

    /** @test */
    public function check_rule_in()
    {
        $this->assertEquals(
            MoveSizeTypeEnum::ruleIn(),
            "in:".MoveSizeTypeEnum::Studio()
            .",".MoveSizeTypeEnum::Bedroom_1()
            .",".MoveSizeTypeEnum::Bedroom_2()
            .",".MoveSizeTypeEnum::Bedroom_3()
            .",".MoveSizeTypeEnum::Bedroom_4()
            .",".MoveSizeTypeEnum::Storage()
        );
    }
}
