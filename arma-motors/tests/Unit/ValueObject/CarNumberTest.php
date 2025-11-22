<?php

namespace Tests\Unit\ValueObject;

use App\ValueObjects\CarNumber;
use Tests\TestCase;

class CarNumberTest extends TestCase
{
    /** @test */
    public function success()
    {
        $number = 'AA1111AA';
        $obj = new CarNumber($number);

        $this->assertEquals($number, $obj);
        $this->assertEquals($number, $obj->getValue());
    }

    /** @test */
    public function success_clear()
    {
        $number = 'aa 1111 aa';
        $numberClear = 'AA1111AA';

        $obj = new CarNumber($number);

        $this->assertNotEquals($number, $obj);
        $this->assertEquals($numberClear, $obj);
    }

    /** @test */
    public function it_compare_same_objects_success()
    {
        $number = 'AA1111AA';
        $number1 = new CarNumber($number);
        $number2 = new CarNumber($number);

        $this->assertTrue($number1->compare($number2));
    }
}
