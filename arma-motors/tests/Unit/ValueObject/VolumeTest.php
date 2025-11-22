<?php

namespace Tests\Unit\ValueObject;

use App\ValueObjects\Money;
use App\ValueObjects\Phone;
use App\ValueObjects\Volume;
use Tests\TestCase;

class VolumeTest extends TestCase
{
    /** @test */
    public function success()
    {
        $data = '22222';

        $obj = new Volume($data);

        $this->assertEquals($data, $obj);
        $this->assertFalse($obj->isConvertToDb());
        $this->assertFalse($obj->isConvertFomDb());
    }

    /** @test */
    public function success_get_value()
    {
        $data = '22222';

        $obj = new Volume($data);

        $this->assertEquals($data, $obj->getValue());
    }

    /** @test */
    public function success_clear()
    {
        $data = '2 2';
        $dataClear = '22';

        $obj = new Volume($data);

        $this->assertNotEquals($data, $obj);
        $this->assertEquals($dataClear, $obj);
    }

    /** @test */
    public function wrong_string()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Volume('sadasdas');
    }

    /** @test */
    public function wrong_string_and_number()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Volume('sadasdas');
    }
}
