<?php

namespace App\GraphQL\Types\Catalog\Brands;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Brands\Brand;

class BrandType extends BaseType
{
    public const NAME = 'BrandType';
    public const MODEL = Brand::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id()
            ],
            'name' => [
                'type' => NonNullType::string(),
            ],
            'slug' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
