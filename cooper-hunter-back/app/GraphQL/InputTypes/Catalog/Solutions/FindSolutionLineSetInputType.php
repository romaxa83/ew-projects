<?php

namespace App\GraphQL\InputTypes\Catalog\Solutions;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionZoneEnumType;
use App\GraphQL\Types\NonNullType;

class FindSolutionLineSetInputType extends BaseInputType
{
    public const NAME = 'FindSolutionLineSetInputType';

    public function fields(): array
    {
        return [
            'line_set_id' => [
                'type' => NonNullType::id()
            ],
            'default_for_zones' => [
                'type' => SolutionZoneEnumType::list(),
            ]
        ];
    }
}
