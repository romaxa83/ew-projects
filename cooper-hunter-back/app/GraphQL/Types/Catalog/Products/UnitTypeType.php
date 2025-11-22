<?php

namespace App\GraphQL\Types\Catalog\Products;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\UnitType;

class UnitTypeType extends BaseType
{
    public const NAME = 'UnitTypeType';
    public const MODEL = UnitType::class;

    public function fields(): array
    {
        $fields = [
            'id' => ['type' => NonNullType::id(),],
            'name' => ['type' => NonNullType::string()],
        ];

        return $fields;
    }
}
