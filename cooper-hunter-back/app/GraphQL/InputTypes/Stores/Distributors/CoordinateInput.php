<?php

namespace App\GraphQL\InputTypes\Stores\Distributors;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class CoordinateInput extends BaseInputType
{
    public const NAME = 'CoordinateInput';

    public function fields(): array
    {
        return [
            'longitude' => [
                'type' => NonNullType::float(),
                'rules' => ['required', 'numeric', 'between:-180,180'],
            ],
            'latitude' => [
                'type' => NonNullType::float(),
                'rules' => ['required', 'numeric', 'between:-90,90'],
            ],
        ];
    }
}
