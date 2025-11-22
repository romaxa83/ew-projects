<?php

namespace Tests\Unit\Dto\Catalog;

use App\Dto\Catalog\Products\ProductFeatureDto;
use Tests\TestCase;

class ProductFeatureDtoTest extends TestCase
{
    /** @test */
    public function success_fill_by_args(): void
    {
        $data = static::data();

        $dto = ProductFeatureDto::byArgs($data);

        $this->assertEquals($dto->getValueId(), $data['value_id']);
    }

    public static function data(): array
    {
        return [
            'value_id' => 2,
        ];
    }
}

