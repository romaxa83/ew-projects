<?php

namespace App\GraphQL\InputTypes\Dictionaries;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class TireWidthInputType extends BaseInputType
{
    public const NAME = 'TireWidthInputType';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'value' => [
                'type' => NonNullType::float(),
            ],
        ];
    }
}
