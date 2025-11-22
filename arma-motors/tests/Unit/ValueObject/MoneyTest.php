<?php

namespace Tests\Unit\ValueObject;

use App\ValueObjects\Money;
use App\ValueObjects\Phone;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    /** @test */
    public function success()
    {
        $money = '22222';

        $obj = new Money($money);

        $this->assertEquals($money, $obj);
        $this->assertFalse($obj->isConvertToDb());
        $this->assertFalse($obj->isConvertFomDb());
    }

    /** @test */
    public function success_get_value()
    {
        $money = '22222';

        $obj = new Money($money);

        $this->assertEquals($money, $obj->getValue());
    }

    /** @test */
    public function success_clear()
    {
        $money = '2 2';
        $moneyClear = '22';

        $obj = new Money($money);

        $this->assertNotEquals($money, $obj);
        $this->assertEquals($moneyClear, $obj);
    }

    /** @test */
    public function wrong_string()
    {
        $this->expectException(\InvalidArgumentException::class);

        $phone = new Phone('sadasdas');
    }

    /** @test */
    public function wrong_string_and_number()
    {
        $this->expectException(\InvalidArgumentException::class);

        $phone = new Phone('sadasdaew23423s');
    }

    /** @test */
    public function convert_to_db()
    {
        $money = 100;
        $obj = Money::instanceToDbConvert($money);

        $this->assertNotEquals($money, $obj->getValue());
        $this->assertEquals(10000, $obj->getValue());
        $this->assertFalse($obj->isConvertFomDb());
        $this->assertTrue($obj->isConvertToDb());
    }

    /** @test */
    public function convert_from_db()
    {
        $money = 100;
        $obj = Money::instanceFromDbConvert($money);

        $this->assertNotEquals($money, $obj->getValue());
        $this->assertEquals(1, $obj->getValue());
        $this->assertTrue($obj->isConvertFomDb());
        $this->assertFalse($obj->isConvertToDb());
    }

    /** @test */
    public function convert__db()
    {
        $money = 100;
        $obj = Money::instanceFromDbConvert($money);

        $this->assertNotEquals($money, $obj->getValue());
        $this->assertEquals(1, $obj->getValue());
        $this->assertTrue($obj->isConvertFomDb());
        $this->assertFalse($obj->isConvertToDb());
    }
}
