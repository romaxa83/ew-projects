<?php

namespace App\GraphQL\InputTypes\Dictionaries;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class TireHeightInputType extends BaseInputType
{
    public const NAME = 'TireHeightInputType';

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
