<?php

namespace App\GraphQL\InputTypes\Catalog\Solutions;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class FindSolutionDefaultSchemaInputType extends BaseInputType
{
    public const NAME = 'FindSolutionDefaultSchemaInputType';

    public function fields(): array
    {
        return [
            'count_zones' => [
                'type' => NonNullType::int(),
                'defaultValue' => 2,
                'rules' => [
                    'required',
                    'int',
                    'min:2',
                    'max:6'
                ]
            ],
            'indoors' => [
                'type' => NonNullType::listOf(
                    NonNullType::id()
                ),
            ],
        ];
    }
}
