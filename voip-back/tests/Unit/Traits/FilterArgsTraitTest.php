<?php

namespace Tests\Unit\Traits;

use App\Traits\FilterArgsTrait;
use Tests\TestCase;

class FilterArgsTraitTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_remove(): void
    {
        $data = [
            'key_1' => 'value_1',
            'key_2' => 'value_1',
            'key_3' => null,
        ];

        $this->assertCount(3, $data);

        $data = FilterArgsTrait::removeNullableArgs($data);

        $this->assertCount(2, $data);
        $this->assertFalse(array_key_exists('key_3', $data));
    }

    /** @test */
    public function empty_data(): void
    {
        $data = [];

        $this->assertEmpty($data);

        $data = FilterArgsTrait::removeNullableArgs($data);

        $this->assertEmpty($data);
    }
}
