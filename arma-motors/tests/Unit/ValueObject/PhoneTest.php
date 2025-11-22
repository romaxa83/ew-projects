<?php

namespace Tests\Unit\ValueObject;

use App\ValueObjects\Phone;
use Tests\TestCase;

class PhoneTest extends TestCase
{
    /** @test */
    public function success()
    {
        $phone = '380999922222';

        $obj = new Phone($phone);

        $this->assertEquals($phone, $obj);
    }

    /** @test */
    public function format_for_aa()
    {
        $phone = '380999922222';
        $phoneForAA = '+380999922222';

        $obj = new Phone($phone);

        $this->assertEquals($phoneForAA, $obj->formatAA());
        $this->assertNotEquals($phone, $obj->formatAA());
    }

    /** @test */
    public function success_clear()
    {
        $phone = '+38(099)9922222';
        $phoneClear = '380999922222';

        $obj = new Phone($phone);

        $this->assertNotEquals($phone, $obj);
        $this->assertEquals($phoneClear, $obj);
    }

    /** @test */
    public function wrong_long()
    {
        $this->expectException(\InvalidArgumentException::class);

        $phone = new Phone('+38(099)9922222777777777777777777777777777');
    }

    /** @test */
    public function not_phone()
    {
        $this->expectException(\InvalidArgumentException::class);

        $phone = new Phone('not phone');
    }

    public function test_it_compare_same_objects_success()
    {
        $phoneString = '+380991234567';
        $phone1 = new Phone($phoneString);
        $phone2 = new Phone($phoneString);

        $this->assertTrue($phone1->compare($phone2));
    }

    public function test_it_compare_same_objects_success_pretty()
    {
        $phoneString = '+380991234567';
        $phoneStringPretty = '380991234567';
        $phone1 = new Phone($phoneString);
        $phone2 = new Phone($phoneStringPretty);

        $this->assertTrue($phone1->compare($phone2));
    }

    public function test_it_compare_not_equals_object_fail()
    {
        $phone1 = new Phone('+380991234567');
        $phone2 = new Phone('+380991234568');

        $this->assertFalse($phone1->compare($phone2));
    }

    public function test_it_has_exception_when_compare_not_same_objects()
    {
        $phone = new Phone('+380991234567');
        $object = new \stdClass();

        $this->expectException(\TypeError::class);

        $phone->compare($object);
    }
}
