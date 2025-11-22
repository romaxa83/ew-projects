<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class PhoneFormatterTest extends TestCase
{
    /**
     * @param $phone
     * @param $expected
     * @dataProvider iItFormatPhoneDataProvider
     */
    public function test_it_format_phone($phone, $expected)
    {
        $this->assertEquals($expected, phone_format($phone));
    }

    public function iItFormatPhoneDataProvider()
    {
        return [
            ['12046869751', '+1 (204) 686-9751'],
            ['120468697511', null],
            ['12046849351', '+1 (204) 684-9351'],
        ];
    }
}
