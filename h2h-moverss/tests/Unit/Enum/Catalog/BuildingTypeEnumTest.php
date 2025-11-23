<?php

namespace Tests\Unit\Enum\Catalog;

use App\Enums\Catalog\BuildingTypeEnum;
use Tests\TestCase;

class BuildingTypeEnumTest extends TestCase
{

    /** @test */
    public function check_value()
    {
        $this->assertEquals('1', BuildingTypeEnum::Home());
        $this->assertEquals('2', BuildingTypeEnum::Apartment());
        $this->assertEquals('3', BuildingTypeEnum::Storage());
        $this->assertEquals('4', BuildingTypeEnum::Office());
    }

    /** @test */
    public function check_label()
    {
        $this->assertEquals('Home', BuildingTypeEnum::Home->labelAsName());
        $this->assertEquals('Apartment', BuildingTypeEnum::Apartment->labelAsName());
        $this->assertEquals('Storage', BuildingTypeEnum::Storage->labelAsName());
        $this->assertEquals('Office', BuildingTypeEnum::Office->labelAsName());
    }

    /** @test */
    public function check_get_label_by_name()
    {
        $this->assertEquals('Home', BuildingTypeEnum::getLabelAsNameByValue(BuildingTypeEnum::Home->value));
        $this->assertEquals('Apartment', BuildingTypeEnum::getLabelAsNameByValue(BuildingTypeEnum::Apartment->value));
        $this->assertEquals('Storage', BuildingTypeEnum::getLabelAsNameByValue(BuildingTypeEnum::Storage->value));
        $this->assertEquals('Office', BuildingTypeEnum::getLabelAsNameByValue(BuildingTypeEnum::Office->value));

        $this->assertNull(BuildingTypeEnum::getLabelAsNameByValue(null));
    }

    /** @test */
    public function check_for_select()
    {
        $this->assertEquals(BuildingTypeEnum::forSelect(), [
            ['id' => BuildingTypeEnum::Home->value, 'title' => BuildingTypeEnum::Home->labelAsName()],
            ['id' => BuildingTypeEnum::Apartment->value, 'title' => BuildingTypeEnum::Apartment->labelAsName()],
            ['id' => BuildingTypeEnum::Storage->value, 'title' => BuildingTypeEnum::Storage->labelAsName()],
            ['id' => BuildingTypeEnum::Office->value, 'title' => BuildingTypeEnum::Office->labelAsName()],
        ]);
    }

    /** @test */
    public function wrong_value_exception()
    {
        $value = '5';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Enum not found by value: ' . $value);

        BuildingTypeEnum::getLabelAsNameByValue($value);
    }


}

