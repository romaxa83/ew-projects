<?php

namespace Tests\Unit\Traits;

use App\Traits\AssetData;
use Tests\TestCase;
use Illuminate\Http\Response;

class AssetDataTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_field_exist_true()
    {
        $trait = new class { use AssetData;};

        $field = 'id';
        $data = ['id' => 3];

        $this->assertTrue($trait::checkFieldExist($data, $field));
    }

    /** @test */
    public function check_field_exist_false()
    {
        $trait = new class { use AssetData;};

        $field = 'status';
        $data = ['id' => 3];

        $this->assertFalse($trait::checkFieldExist($data, $field));
    }

    /** @test */
    public function assert_field_exist_exception()
    {
        $trait = new class { use AssetData;};

        $field = 'status';
        $data = ['id' => 3];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("field [{$field}] must exist");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        $this->assertFalse($trait::assetFieldExist($data, $field));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function assert_field_exist_nothing()
    {
        $trait = new class { use AssetData;};

        $field = 'id';
        $data = ['id' => 3];

        $trait::assetFieldExist($data, $field);
    }

    /** @test */
    public function assert_field_not_null_exception()
    {
        $trait = new class { use AssetData;};

        $field = 'id';
        $data = ['id' => null];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("field [{$field}] can\'t be null");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        $this->assertFalse($trait::assetFieldNotNull($data, $field));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function assert_field_not_null_nothing()
    {
        $trait = new class { use AssetData;};

        $field = 'id';
        $data = ['id' => 3];

        $trait::assetFieldNotNull($data, $field);
    }

    /** @test */
    public function assert_field_empty_exception()
    {
        $trait = new class { use AssetData;};

        $field = 'id';
        $data = ['id' => ''];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("field [{$field}] can\'t be empty");
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        $this->assertFalse($trait::assetFieldNotEmpty($data, $field));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function assert_field_empty_nothing()
    {
        $trait = new class { use AssetData;};

        $field = 'id';
        $data = ['id' => 3];

        $trait::assetFieldNotEmpty($data, $field);
    }
}
