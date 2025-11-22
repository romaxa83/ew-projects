<?php

namespace Tests\Unit\ValueObject;

use App\ValueObjects\CarVin;
use Tests\TestCase;

class CarVinTest extends TestCase
{
    /** @test */
    public function success()
    {
        $vin = 'AA3434YFDGFD5745';
        $obj = new CarVin($vin);

        $this->assertEquals($vin, $obj);
        $this->assertEquals($vin, $obj->getValue());
    }

    /** @test */
    public function success_clear()
    {
        $vin = 'AAAA2222 22aa';
        $vinClear = 'AAAA222222AA';

        $obj = new CarVin($vin);

        $this->assertNotEquals($vin, $obj);
        $this->assertEquals($vinClear, $obj);
    }

    /** @test */
    public function it_compare_same_objects_success()
    {
        $vin = 'AAAA4444AAAAA';
        $vin1 = new CarVin($vin);
        $vin2 = new CarVin($vin);

        $this->assertTrue($vin1->compare($vin2));
    }
}
