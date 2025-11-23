<?php

namespace Tests\Unit\Enum\Catalog;

use App\Enums\Catalog\ParkingTypeEnum;
use Tests\TestCase;

class ParkingTypeEnumTest extends TestCase
{

    /** @test */
    public function check_value()
    {
        $this->assertEquals('1', ParkingTypeEnum::No_parking());
        $this->assertEquals('2', ParkingTypeEnum::Loading_dock());
        $this->assertEquals('3', ParkingTypeEnum::Parking_zone());
        $this->assertEquals('4', ParkingTypeEnum::Street_parking());
        $this->assertEquals('5', ParkingTypeEnum::Alley_parking());
    }

    /** @test */
    public function check_label()
    {
        $this->assertEquals('No parking', ParkingTypeEnum::No_parking->labelAsName());
        $this->assertEquals('Loading dock', ParkingTypeEnum::Loading_dock->labelAsName());
        $this->assertEquals('Parking zone', ParkingTypeEnum::Parking_zone->labelAsName());
        $this->assertEquals('Street parking', ParkingTypeEnum::Street_parking->labelAsName());
        $this->assertEquals('Alley parking', ParkingTypeEnum::Alley_parking->labelAsName());
    }

    /** @test */
    public function check_get_label_by_name()
    {
        $this->assertEquals('No parking', ParkingTypeEnum::getLabelAsNameByValue(ParkingTypeEnum::No_parking->value));
        $this->assertEquals('Loading dock', ParkingTypeEnum::getLabelAsNameByValue(ParkingTypeEnum::Loading_dock->value));
        $this->assertEquals('Parking zone', ParkingTypeEnum::getLabelAsNameByValue(ParkingTypeEnum::Parking_zone->value));
        $this->assertEquals('Street parking', ParkingTypeEnum::getLabelAsNameByValue(ParkingTypeEnum::Street_parking->value));
        $this->assertEquals('Alley parking', ParkingTypeEnum::getLabelAsNameByValue(ParkingTypeEnum::Alley_parking->value));

        $this->assertNull(ParkingTypeEnum::getLabelAsNameByValue(null));
    }

    /** @test */
    public function check_for_select()
    {
        $this->assertEquals(ParkingTypeEnum::forSelect(), [
            ['id' => ParkingTypeEnum::No_parking->value, 'title' => ParkingTypeEnum::No_parking->labelAsName()],
            ['id' => ParkingTypeEnum::Loading_dock->value, 'title' => ParkingTypeEnum::Loading_dock->labelAsName()],
            ['id' => ParkingTypeEnum::Parking_zone->value, 'title' => ParkingTypeEnum::Parking_zone->labelAsName()],
            ['id' => ParkingTypeEnum::Street_parking->value, 'title' => ParkingTypeEnum::Street_parking->labelAsName()],
            ['id' => ParkingTypeEnum::Alley_parking->value, 'title' => ParkingTypeEnum::Alley_parking->labelAsName()],
        ]);
    }

    /** @test */
    public function wrong_value_exception()
    {
        $value = '6';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Enum not found by value: ' . $value);

        ParkingTypeEnum::getLabelAsNameByValue($value);
    }


}

