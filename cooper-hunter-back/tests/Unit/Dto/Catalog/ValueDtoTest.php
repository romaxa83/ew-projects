<?php

namespace Tests\Unit\Dto\Catalog;

use App\Dto\Catalog\ValueDto;
use App\Models\Catalog\Features\Value;
use Tests\TestCase;

class ValueDtoTest extends TestCase
{
    /** @test */
    public function success_fill_by_args(): void
    {
        $data = static::data();

        $dto = ValueDto::byArgs($data);

        $this->assertEquals($dto->getActive(), $data['active']);
    }

    public static function data(): array
    {
        return [
            'feature_id' => 1,
            'sort' => 2,
            'active' => false,
            'title' => 'title',
        ];
    }

    /** @test */
    public function success_fill_by_args_without_active(): void
    {
        $data = static::data();
        unset($data['active']);

        $dto = ValueDto::byArgs($data);

        $this->assertEquals(Value::DEFAULT_ACTIVE, $dto->getActive());
    }
}
