<?php

namespace Tests\Unit\Helpers;

use App\Helpers\ConvertNumber;
use Tests\TestCase;

class ConvertNumberTest extends TestCase
{
    /** @test */
    public function convert_float_to_number_success()
    {
        $float = 3.5;
        $int = ConvertNumber::fromFloatToNumber($float);

        $this->assertEquals($int, 35);
    }

    /** @test */
    public function convert_float_to_number_success_as_string()
    {
        $float = "3.5";
        $int = ConvertNumber::fromFloatToNumber($float);

        $this->assertEquals($int, 35);
    }

    /** @test */
    public function convert_float_to_number_success_as_int()
    {
        $float = "3";
        $int = ConvertNumber::fromFloatToNumber($float);

        $this->assertEquals($int, 30);
    }

    /** @test */
    public function convert_number_to_float_success()
    {
        $number = 33;
        $float = ConvertNumber::fromNumberToFloat($number);

        $this->assertEquals($float, 3.3);
    }

    /** @test */
    public function convert_number_to_float_success_as_string()
    {
        $number = "33";
        $float = ConvertNumber::fromNumberToFloat($number);

        $this->assertEquals($float, 3.3);
    }

    /** @test */
    public function convert_number_to_float_success_as__number()
    {
        $number = 30;
        $float = ConvertNumber::fromNumberToFloat($number);

        $this->assertEquals($float, 3.0);
    }
}


