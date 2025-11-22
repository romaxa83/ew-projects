<?php

namespace App\GraphQL\Types\Catalog\Solutions;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class FindSolutionLineSetType extends BaseType
{
    public const NAME = 'FindSolutionLineSetType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'description' => 'Solution ID (not product ID)',
            ],
            'short_name' => [
                'type' => Type::string(),
            ],
            'product' => [
                'type' => ProductType::nonNullType(),
            ],
            'default' => [
                'type' => NonNullType::boolean(),
            ]
        ];
    }
}
