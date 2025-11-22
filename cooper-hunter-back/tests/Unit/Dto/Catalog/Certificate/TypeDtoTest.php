<?php

namespace Tests\Unit\Dto\Catalog\Certificate;

use App\Dto\Catalog\Certificate\TypeDto;
use App\Exceptions\AssertDataException;
use Tests\TestCase;

class TypeDtoTest extends TestCase
{
    /** @test */
    public function success_fill_by_args()
    {
        $data = static::data();

        $dto = TypeDto::byArgs($data);

        $this->assertEquals($dto->getType(), $data['type']);
    }

    /** @test */
    public function success_fill_by_args_without_type()
    {
        $data = static::data();
        unset($data['type']);

        $this->expectException(AssertDataException::class);
        $this->expectExceptionMessage(__('exceptions.assert_data.field must exist', ['field' => 'type']));

        TypeDto::byArgs($data);
    }

    public static function data()
    {
        return [
            'type' => "some type",
        ];
    }
}

