<?php

namespace App\GraphQL\Types\Catalog\Solutions;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionIndoorEnumType;
use App\GraphQL\Types\NonNullType;

class FindSolutionIndoorType extends BaseType
{
    public const NAME = 'FindSolutionIndoorType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'description' => 'Solution ID (not product ID)',
            ],
            'series' => [
                'type' => SolutionSeriesType::nonNullType(),
            ],
            'type' => [
                'type' => SolutionIndoorEnumType::nonNullType(),
            ],
            'btu' => [
                'type' => NonNullType::int(),
            ],
            'product' => [
                'type' => ProductType::nonNullType(),
            ],
            'line_sets' => [
                'type' => FindSolutionLineSetType::nonNullList(),
            ]
        ];
    }
}
