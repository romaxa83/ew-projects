<?php

namespace App\GraphQL\Types\Catalog\Solutions;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionIndoorEnumType;
use App\GraphQL\Types\NonNullType;

class SolutionIndoorSettingType extends BaseType
{
    public const NAME = 'SolutionIndoorSettingType';

    public function fields(): array
    {
        return [
            'series' => [
                'type' => SolutionSeriesType::nonNullType(),
            ],
            'btu' => [
                'type' => NonNullType::int()
            ],
            'types' => [
                'type' => NonNullType::listOf(
                    SolutionIndoorEnumType::nonNullType()
                )
            ]
        ];
    }
}
