<?php

namespace App\GraphQL\InputTypes\Stores\Distributors;

use GraphQL\Type\Definition\Type;

class CoordinateInRadiusFilterInput extends CoordinateInput
{
    public const NAME = 'CoordinateInRadiusFilterInput';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'radius' => [
                    'type' => Type::int(),
                    'description' => 'Filter by "coordinates" in a radius (in kilometers). 500 km by default',
                    'defaultValue' => 500,
                ],
            ]
        );
    }
}
