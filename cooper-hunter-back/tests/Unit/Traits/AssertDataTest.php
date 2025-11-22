<?php

namespace Tests\Unit\Traits;

use App\Exceptions\AssertDataException;
use App\Traits\AssertData;
use Tests\TestCase;

class AssertDataTest extends TestCase
{
    use AssertData;

    /** @test */
    public function check_field_exist()
    {
        $data = ['name' => 'some name'];

        $resultTrue = static::checkFieldExist($data, 'name');
        $resultFalse = static::checkFieldExist($data, 'key');

        $this->assertTrue($resultTrue);
        $this->assertFalse($resultFalse);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function assert_field_exist()
    {
        $data = ['name' => 'some name'];

        static::fieldExist($data, 'name');
    }

    /** @test */
    public function fail_assert_field_exist()
    {
        $fieldFail = 'key';
        $data = ['name' => 'some name'];

        $this->expectException(AssertDataException::class);
        $this->expectExceptionMessage(__('exceptions.assert_data.field must exist', ['field' => $fieldFail]));

        static::fieldExist($data, $fieldFail);
    }

    /** @test */
    public function check_field_not_null()
    {
        $data = ['name' => 'some data'];
        $resultTrue = static::checkFieldNotNull($data, 'name');

        $data['name'] = null;
        $resultFalse = static::checkFieldNotNull($data, 'name');

        $this->assertTrue($resultTrue);
        $this->assertFalse($resultFalse);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function assert_field_not_null()
    {
        $data = ['name' => 'some name'];

        static::fieldNotNull($data, 'name');
    }

    /** @test */
    public function fail_assert_field_not_null()
    {
        $data = ['name' => null];

        $this->expectException(AssertDataException::class);
        $this->expectExceptionMessage(__('exceptions.assert_data.field not null', ['field' => 'name']));

        static::fieldNotNull($data, 'name');
    }

    /** @test */
    public function check_field_not_empty()
    {
        $data = ['name' => 'some data'];
        $resultTrue = static::checkFieldNotEmpty($data, 'name');

        $data['name'] = '';
        $resultFalse = static::checkFieldNotEmpty($data, 'name');

        $this->assertTrue($resultTrue);
        $this->assertFalse($resultFalse);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function assert_field_not_empty()
    {
        $data = ['name' => 'some name'];

        static::fieldNotEmpty($data, 'name');
    }

    /** @test */
    public function fail_assert_field_not_empty()
    {
        $data = ['name' => ''];

        $this->expectException(AssertDataException::class);
        $this->expectExceptionMessage(__('exceptions.assert_data.field not empty', ['field' => 'name']));

        static::fieldNotEmpty($data, 'name');
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function asset_field()
    {
        $data = ['name' => 'some data'];

        static::assetField($data, 'name');
    }

    /** @test */
    public function fail_assert_field_()
    {
        $data = [];

        $this->expectException(AssertDataException::class);
        $this->expectExceptionMessage(__('exceptions.assert_data.field must exist', ['field' => 'name']));

        static::assetField($data, 'name');

        $data['name'] = null;
        $this->expectException(AssertDataException::class);
        $this->expectExceptionMessage(__('exceptions.assert_data.field not null', ['field' => 'name']));

        static::assetField($data, 'name');

        $data = ['name' => ''];

        $this->expectException(AssertDataException::class);
        $this->expectExceptionMessage(__('exceptions.assert_data.field not empty', ['field' => 'name']));

        static::assetField($data, 'name');
    }
}
