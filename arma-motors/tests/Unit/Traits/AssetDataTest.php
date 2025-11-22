<?php

namespace Tests\Unit\Traits;

use App\Traits\AssetData;
use Tests\TestCase;

class AssetDataTest extends TestCase
{
    use AssetData;

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function success_assert_field_exist()
    {
        $data = [
            'name' => 'test'
        ];

        static::assetFieldExist($data, 'name');

    }

    /**
     * @test
     */
    public function fail_assert_field_exist()
    {
        $data = [
            'name' => 'test'
        ];

        $this->expectException(\InvalidArgumentException::class);

        static::assetFieldExist($data, 'name_rr');
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function success_assert_field_not_null()
    {
        $data = [
            'name' => 'test'
        ];

        static::assetFieldNotNull($data, 'name');

    }

    /**
     * @test
     * @doesNotPerformAssertions
     *
     */
    public function success_assert_field_not_null_if_empty()
    {
        $data = [
            'name' => ''
        ];

        static::assetFieldNotNull($data, 'name');
    }

    /**
     * @test
     */
    public function fail_assert_field_not_null()
    {
        $data = [
            'name' => null
        ];

        $this->expectException(\InvalidArgumentException::class);

        static::assetFieldNotNull($data, 'name');
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function success_assert_field_not_empty()
    {
        $data = [
            'name' => 'test'
        ];

        static::assetFieldNotNull($data, 'name');

    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function fail_assert_field_not_empty_if_empty()
    {
        $data = [
            'name' => ''
        ];

        static::assetFieldNotNull($data, 'name');
    }

    /**
     * @test
     */
    public function fail_assert_field_not_empty()
    {
        $data = [
            'name' => null
        ];

        $this->expectException(\InvalidArgumentException::class);

        static::assetFieldNotNull($data, 'name');
    }

    /**
     * @test
     */
    public function success_check_field_not_empty()
    {
        $data = [
            'name' => 'dddd'
        ];
        $this->assertTrue(static::checkFieldExist($data, 'name'));

        $data = [
            'name' => ''
        ];
        $this->assertTrue(static::checkFieldExist($data, 'name'));

        $data = [
            'name' => null
        ];
        $this->assertTrue(static::checkFieldExist($data, 'name'));


        $data = [
            'name' => 'wew'
        ];
        $this->assertFalse(static::checkFieldExist($data, 'name_test'));
    }


}

