<?php


namespace App\GraphQL\Types\Catalog\Solutions;


use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class SolutionDefaultSchemaType extends BaseType
{
    public const NAME = 'SolutionDefaultSchemaType';

    public function fields(): array
    {
        return [
            'count_zones' => [
                'type' => NonNullType::int()
            ],
            'indoors' => [
                'type' => SolutionType::nonNullList()
            ]
        ];
    }
}
