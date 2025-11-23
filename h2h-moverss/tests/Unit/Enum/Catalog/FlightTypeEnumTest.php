<?php

namespace Tests\Unit\Enum\Catalog;

use App\Enums\Catalog\FlightTypeEnum;
use Tests\TestCase;

class FlightTypeEnumTest extends TestCase
{

    /** @test */
    public function check_value()
    {
        $this->assertEquals('1', FlightTypeEnum::Flight_1());
        $this->assertEquals('2', FlightTypeEnum::Flight_2());
        $this->assertEquals('3', FlightTypeEnum::Flight_3());
        $this->assertEquals('4', FlightTypeEnum::Flight_4());
        $this->assertEquals('5', FlightTypeEnum::Flight_5());
        $this->assertEquals('6', FlightTypeEnum::Flight_05());
        $this->assertEquals('7', FlightTypeEnum::Flight_15());
    }

    /** @test */
    public function check_label()
    {
        $this->assertEquals('1 flight', FlightTypeEnum::Flight_1->labelAsName());
        $this->assertEquals('2 flights', FlightTypeEnum::Flight_2->labelAsName());
        $this->assertEquals('3 flights', FlightTypeEnum::Flight_3->labelAsName());
        $this->assertEquals('4 flights', FlightTypeEnum::Flight_4->labelAsName());
        $this->assertEquals('5+ flights', FlightTypeEnum::Flight_5->labelAsName());
        $this->assertEquals('0.5 flight', FlightTypeEnum::Flight_05->labelAsName());
        $this->assertEquals('1.5 flight', FlightTypeEnum::Flight_15->labelAsName());
    }

    /** @test */
    public function check_get_label_by_name()
    {
        $this->assertEquals(
            '1 flight',
            FlightTypeEnum::getLabelAsNameByValue(FlightTypeEnum::Flight_1->value)
        );
        $this->assertEquals(
            '2 flights',
            FlightTypeEnum::getLabelAsNameByValue(FlightTypeEnum::Flight_2->value)
        );
        $this->assertEquals(
            '3 flights',
            FlightTypeEnum::getLabelAsNameByValue(FlightTypeEnum::Flight_3->value)
        );
        $this->assertEquals(
            '4 flights',
            FlightTypeEnum::getLabelAsNameByValue(FlightTypeEnum::Flight_4->value)
        );
        $this->assertEquals(
            '5+ flights',
            FlightTypeEnum::getLabelAsNameByValue(FlightTypeEnum::Flight_5->value)
        );
        $this->assertEquals(
            '0.5 flight',
            FlightTypeEnum::getLabelAsNameByValue(FlightTypeEnum::Flight_05->value)
        );
        $this->assertEquals(
            '1.5 flight',
            FlightTypeEnum::getLabelAsNameByValue(FlightTypeEnum::Flight_15->value)
        );

        $this->assertNull(FlightTypeEnum::getLabelAsNameByValue(null));
    }

    /** @test */
    public function check_for_select()
    {
        $this->assertEquals(FlightTypeEnum::forSelect(), [
            [
                'id' => FlightTypeEnum::Flight_05->value,
                'title' => FlightTypeEnum::Flight_05->labelAsName(),
                'sort' => '1'
            ],
            [
                'id' => FlightTypeEnum::Flight_1->value,
                'title' => FlightTypeEnum::Flight_1->labelAsName(),
                'sort' => '2'
            ],
            [
                'id' => FlightTypeEnum::Flight_15->value,
                'title' => FlightTypeEnum::Flight_15->labelAsName(),
                'sort' => '3'
            ],
            [
                'id' => FlightTypeEnum::Flight_2->value,
                'title' => FlightTypeEnum::Flight_2->labelAsName(),
                'sort' => '4'
            ],
            [
                'id' => FlightTypeEnum::Flight_3->value,
                'title' => FlightTypeEnum::Flight_3->labelAsName(),
                'sort' => '5'
            ],
            [
                'id' => FlightTypeEnum::Flight_4->value,
                'title' => FlightTypeEnum::Flight_4->labelAsName(),
                'sort' => '6'
            ],
            [
                'id' => FlightTypeEnum::Flight_5->value,
                'title' => FlightTypeEnum::Flight_5->labelAsName(),
                'sort' => '7'
            ],
        ]);
    }

    /** @test */
    public function wrong_value_exception()
    {
        $value = '64';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Enum not found by value: ' . $value);

        FlightTypeEnum::getLabelAsNameByValue($value);
    }
}
