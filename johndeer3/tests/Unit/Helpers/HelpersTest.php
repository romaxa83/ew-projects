<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class HelpersTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_array_to_json(): void
    {
        $arr = [
            'string' => 'some_value',
            'number' => 30,
            "nested" => [
                'sub_key' => 'sub_value'
            ]
        ];

        $data = array_to_json($arr);

        $this->assertEquals("{\"string\":\"some_value\",\"number\":30,\"nested\":{\"sub_key\":\"sub_value\"}}", $data);
    }

    /** @test */
    public function success_array_empty(): void
    {
        $arr = [];

        $data = array_to_json($arr);

        $this->assertEquals("[]", $data);
    }
}

