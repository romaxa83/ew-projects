<?php

namespace App\GraphQL\Types\Catalog\Solutions;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionZoneEnumType;

class SolutionLineSetType extends BaseType
{
    public const NAME = 'SolutionLineSetType';

    public function fields(): array
    {
        return [
            'line_set' => [
                'type' => SolutionType::nonNullType(),
            ],
            'default_for_zones' => [
                'type' => SolutionZoneEnumType::list()
            ]
        ];
    }
}
