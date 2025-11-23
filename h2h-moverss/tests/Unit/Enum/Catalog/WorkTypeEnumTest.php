<?php

namespace Tests\Unit\Enum\Catalog;

use App\Enums\Catalog\WorkTypeEnum;
use Tests\TestCase;

class WorkTypeEnumTest extends TestCase
{

    /** @test */
    public function check_value()
    {
        $this->assertEquals('1', WorkTypeEnum::Moving());
        $this->assertEquals('2', WorkTypeEnum::Packing());
        $this->assertEquals('3', WorkTypeEnum::Loading());
        $this->assertEquals('4', WorkTypeEnum::Unloading());
        $this->assertEquals('5', WorkTypeEnum::Rearrangement());
        $this->assertEquals('6', WorkTypeEnum::Junk());
        $this->assertEquals('8', WorkTypeEnum::Unpacking());
        $this->assertEquals('9', WorkTypeEnum::In_Home_Estimate());
    }

    /** @test */
    public function check_label()
    {
        $this->assertEquals('Moving', WorkTypeEnum::Moving->labelAsName());
        $this->assertEquals('Packing', WorkTypeEnum::Packing->labelAsName());
        $this->assertEquals('Loading', WorkTypeEnum::Loading->labelAsName());
        $this->assertEquals('Unloading', WorkTypeEnum::Unloading->labelAsName());
        $this->assertEquals('Rearrangement', WorkTypeEnum::Rearrangement->labelAsName());
        $this->assertEquals('Junk', WorkTypeEnum::Junk->labelAsName());
        $this->assertEquals('Unpacking', WorkTypeEnum::Unpacking->labelAsName());
        $this->assertEquals('In-Home Estimate', WorkTypeEnum::In_Home_Estimate->labelAsName());
    }

    /** @test */
    public function check_get_label_by_name()
    {
        $this->assertEquals(
            'Moving',
            WorkTypeEnum::getLabelAsNameByValue(WorkTypeEnum::Moving->value)
        );
        $this->assertEquals(
            'Packing',
            WorkTypeEnum::getLabelAsNameByValue(WorkTypeEnum::Packing->value)
        );
        $this->assertEquals(
            'Loading',
            WorkTypeEnum::getLabelAsNameByValue(WorkTypeEnum::Loading->value)
        );
        $this->assertEquals(
            'Unloading',
            WorkTypeEnum::getLabelAsNameByValue(WorkTypeEnum::Unloading->value)
        );
        $this->assertEquals(
            'Rearrangement',
            WorkTypeEnum::getLabelAsNameByValue(WorkTypeEnum::Rearrangement->value)
        );
        $this->assertEquals(
            'Junk',
            WorkTypeEnum::getLabelAsNameByValue(WorkTypeEnum::Junk->value)
        );
        $this->assertEquals(
            'Unpacking',
            WorkTypeEnum::getLabelAsNameByValue(WorkTypeEnum::Unpacking->value)
        );
        $this->assertEquals(
            'In-Home Estimate',
            WorkTypeEnum::getLabelAsNameByValue(WorkTypeEnum::In_Home_Estimate->value)
        );

        $this->assertNull(WorkTypeEnum::getLabelAsNameByValue(null));
    }

    /** @test */
    public function check_for_select()
    {
        $this->assertEquals(WorkTypeEnum::forSelect(), [
            [
                'id' => WorkTypeEnum::Moving->value,
                'title' => WorkTypeEnum::Moving->labelAsName(),
            ],
            [
                'id' => WorkTypeEnum::Packing->value,
                'title' => WorkTypeEnum::Packing->labelAsName(),
            ],
            [
                'id' => WorkTypeEnum::Loading->value,
                'title' => WorkTypeEnum::Loading->labelAsName(),
            ],
            [
                'id' => WorkTypeEnum::Unloading->value,
                'title' => WorkTypeEnum::Unloading->labelAsName(),
            ],
            [
                'id' => WorkTypeEnum::Rearrangement->value,
                'title' => WorkTypeEnum::Rearrangement->labelAsName(),
            ],
            [
                'id' => WorkTypeEnum::Junk->value,
                'title' => WorkTypeEnum::Junk->labelAsName(),
            ],
            [
                'id' => WorkTypeEnum::Unpacking->value,
                'title' => WorkTypeEnum::Unpacking->labelAsName(),
            ],
            [
                'id' => WorkTypeEnum::In_Home_Estimate->value,
                'title' => WorkTypeEnum::In_Home_Estimate->labelAsName(),
            ],
        ]);
    }

    /** @test */
    public function wrong_value_exception()
    {
        $value = '64';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Enum not found by value: ' . $value);

        WorkTypeEnum::getLabelAsNameByValue($value);
    }
}
